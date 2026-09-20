<?php

namespace Tests\Feature\Managers\Users;

use App\Events\Auth\UserCreated;
use App\Events\Auth\UserDeactivated;
use App\Events\Auth\UserDeleted;
use App\Events\Auth\UserPasswordChanged;
use App\Events\Auth\UserReactivated;
use App\Events\Auth\UserUpdated;
use App\Models\Enterprise\Enterprise;
use App\Models\Enterprise\EnterpriseUser;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Techo de rol en UsersController: un actor no puede asignar (crear/actualizar)
 * un rol cuyo conjunto de permisos exceda el suyo propio, ni tocar cuentas
 * superadmin. Regresión del hallazgo crítico #1 de la auditoría de Usuarios.
 */
class UsersControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    /**
     * Manager con la columna role='manager' (pasa el middleware IsManager) pero
     * con permisos Spatie restringidos a solo lo necesario para el CRUD de
     * usuarios — NO tiene el resto de permisos que el rol Spatie 'manager'
     * otorga (ej. orders.delete, invoices.*, roles.*).
     */
    private function limitedActor(): User
    {
        $user = User::factory()->manager()->create();
        $user->syncRoles([]);
        $user->syncPermissions(['users.view', 'users.update', 'users.create']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $user;
    }

    public function test_limited_manager_cannot_escalate_a_user_to_manager_via_update(): void
    {
        $actor = $this->limitedActor();
        $target = User::factory()->customer()->create();

        $response = $this->actingAs($actor)->post(route('manager.users.update'), [
            'slack' => $target->slack,
            'firstname' => $target->firstname,
            'lastname' => $target->lastname,
            'email' => $target->email,
            'identification' => $target->identification,
            'role' => 'manager',
            'available' => '1',
        ]);

        $response->assertOk()->assertJson([
            'success' => false,
            'message' => 'No tienes privilegios suficientes para asignar este rol.',
        ]);

        $this->assertSame('customer', $target->fresh()->role);
    }

    public function test_limited_manager_cannot_escalate_a_user_to_manager_via_store(): void
    {
        $actor = $this->limitedActor();

        $response = $this->actingAs($actor)->post(route('manager.users.store'), [
            'firstname' => 'Nuevo',
            'lastname' => 'Admin',
            'email' => 'nuevo.admin@test.com',
            'role' => 'manager',
            'password' => 'password123',
        ]);

        $response->assertForbidden();
        $this->assertNull(User::where('email', 'nuevo.admin@test.com')->first());
    }

    public function test_full_manager_can_still_promote_a_user_to_manager(): void
    {
        // Regresión de negocio: un manager con el rol Spatie completo (caso
        // normal, no restringido) debe poder seguir asignando 'manager' como
        // hasta ahora — el techo solo bloquea a actores con permisos inferiores.
        $actor = User::factory()->manager()->create();
        $target = User::factory()->customer()->create();

        $this->actingAs($actor)->post(route('manager.users.update'), [
            'slack' => $target->slack,
            'firstname' => $target->firstname,
            'lastname' => $target->lastname,
            'email' => $target->email,
            'identification' => $target->identification,
            'role' => 'manager',
            'available' => '1',
        ])->assertOk()->assertJson(['success' => true]);

        $this->assertSame('manager', $target->fresh()->role);
    }

    public function test_update_rejects_role_outside_whitelist(): void
    {
        $actor = User::factory()->manager()->create();
        $target = User::factory()->customer()->create();

        $this->actingAs($actor)->post(route('manager.users.update'), [
            'slack' => $target->slack,
            'firstname' => $target->firstname,
            'lastname' => $target->lastname,
            'email' => $target->email,
            'identification' => $target->identification,
            'role' => 'superadmin',
            'available' => '1',
        ])->assertOk()->assertJson([
            'success' => false,
            'message' => 'El rol seleccionado no es válido.',
        ]);

        $this->assertSame('customer', $target->fresh()->role);
    }

    public function test_non_superadmin_manager_cannot_update_a_superadmin_account(): void
    {
        $actor = User::factory()->manager()->create();
        $target = User::factory()->role('superadmin')->create();

        $this->actingAs($actor)->post(route('manager.users.update'), [
            'slack' => $target->slack,
            'firstname' => $target->firstname,
            'lastname' => $target->lastname,
            'email' => $target->email,
            'identification' => $target->identification,
            'role' => 'manager',
            'available' => '1',
        ])->assertForbidden();

        $this->assertSame('superadmin', $target->fresh()->role);
    }

    public function test_non_superadmin_manager_cannot_destroy_a_superadmin_account(): void
    {
        $actor = User::factory()->manager()->create();
        $target = User::factory()->role('superadmin')->create();

        $this->actingAs($actor)
            ->delete(route('manager.users.destroy', $target->slack))
            ->assertForbidden();

        $this->assertNotNull($target->fresh());
    }

    public function test_superadmin_can_still_update_another_superadmin_account(): void
    {
        $actor = User::factory()->role('superadmin')->create();
        $target = User::factory()->role('superadmin')->create();

        $this->actingAs($actor)->post(route('manager.users.update'), [
            'slack' => $target->slack,
            'firstname' => 'Actualizado',
            'lastname' => $target->lastname,
            'email' => $target->email,
            'identification' => $target->identification,
            'role' => 'manager',
            'available' => '1',
        ])->assertOk()->assertJson(['success' => true]);

        $this->assertSame('manager', $target->fresh()->role);
    }

    public function test_update_hashes_password_exactly_once(): void
    {
        $actor = User::factory()->manager()->create();
        $target = User::factory()->customer()->create();
        $newPassword = 'brandNewPassword123';

        // UsersController::update() intenta crear/actualizar una relación
        // EnterpriseUser para cualquier usuario no-'enterprise' que no tenga ya
        // una (bug preexistente fuera de alcance: la rama debería aplicar solo
        // a role='enterprise'). Se precrea la relación para no ejercitar esa
        // rama rota y poder probar el hash de password de forma aislada.
        $enterprise = Enterprise::create([
            'slack' => (string) Str::uuid(),
            'title' => 'Empresa de prueba',
            'available' => 1,
        ]);
        EnterpriseUser::create([
            'user_id' => $target->id,
            'enterprise_id' => $enterprise->id,
            'available' => 1,
        ]);

        $this->actingAs($actor)->post(route('manager.users.update'), [
            'slack' => $target->slack,
            'firstname' => $target->firstname,
            'lastname' => $target->lastname,
            'email' => $target->email,
            'identification' => $target->identification,
            'role' => 'customer',
            'available' => '1',
            'password' => $newPassword,
            'enterprises' => (string) $enterprise->id,
        ])->assertOk()->assertJson(['success' => true]);

        $this->assertTrue(
            Hash::check($newPassword, $target->fresh()->password),
            'La contraseña debe quedar hasheada una sola vez (mutator Attribute de User).'
        );
    }

    public function test_update_rejects_duplicate_email(): void
    {
        $actor = User::factory()->manager()->create();
        $other = User::factory()->customer()->create(['email' => 'ocupado@test.com']);
        $target = User::factory()->customer()->create();

        $this->actingAs($actor)->post(route('manager.users.update'), [
            'slack' => $target->slack,
            'firstname' => $target->firstname,
            'lastname' => $target->lastname,
            'email' => 'ocupado@test.com',
            'identification' => $target->identification,
            'role' => 'customer',
            'available' => '1',
        ])->assertOk()->assertJson([
            'success' => false,
            'message' => 'El correo electrónico ya está registrado en nuestro sistema',
        ]);

        $this->assertNotSame('ocupado@test.com', $target->fresh()->email);
    }

    // ── Regresión: eventos de auditoría App\Events\Auth\User* existían con ──
    // ── listener ya armado (App\Listeners\User\UserEventListener) pero ──────
    // ── nunca se disparaban desde ningún controller -- se conectaron en los ──
    // ── puntos reales de mutación de UsersController (store/update/destroy) ──

    public function test_store_dispatches_user_created(): void
    {
        Event::fake([UserCreated::class]);
        $actor = User::factory()->manager()->create();

        $this->actingAs($actor)->post(route('manager.users.store'), [
            'firstname' => 'Nuevo',
            'lastname' => 'Cliente',
            'email' => 'nuevo.cliente@test.com',
            'role' => 'customer',
            'password' => 'password123',
        ])->assertOk()->assertJson(['success' => true]);

        Event::assertDispatched(UserCreated::class, function ($event) {
            return $event->user->email === 'nuevo.cliente@test.com';
        });
    }

    public function test_update_dispatches_user_updated_but_not_password_or_availability_events_when_unchanged(): void
    {
        Event::fake([UserUpdated::class, UserPasswordChanged::class, UserDeactivated::class, UserReactivated::class]);
        $actor = User::factory()->manager()->create();
        $target = User::factory()->customer()->create(['available' => 1]);

        $this->actingAs($actor)->post(route('manager.users.update'), [
            'slack' => $target->slack,
            'firstname' => $target->firstname,
            'lastname' => $target->lastname,
            'email' => $target->email,
            'identification' => $target->identification,
            'role' => 'manager',
            'available' => '1',
        ])->assertOk()->assertJson(['success' => true]);

        Event::assertDispatched(UserUpdated::class);
        Event::assertNotDispatched(UserPasswordChanged::class);
        Event::assertNotDispatched(UserDeactivated::class);
        Event::assertNotDispatched(UserReactivated::class);
    }

    public function test_update_dispatches_user_password_changed_only_when_password_sent(): void
    {
        Event::fake([UserPasswordChanged::class]);
        $actor = User::factory()->manager()->create();
        $target = User::factory()->customer()->create(['available' => 1]);

        $this->actingAs($actor)->post(route('manager.users.update'), [
            'slack' => $target->slack,
            'firstname' => $target->firstname,
            'lastname' => $target->lastname,
            'email' => $target->email,
            'identification' => $target->identification,
            'role' => 'manager',
            'available' => '1',
            'password' => 'otraPassword123',
        ])->assertOk()->assertJson(['success' => true]);

        Event::assertDispatched(UserPasswordChanged::class);
    }

    public function test_update_dispatches_user_deactivated_when_available_flips_to_zero(): void
    {
        Event::fake([UserDeactivated::class, UserReactivated::class]);
        $actor = User::factory()->manager()->create();
        $target = User::factory()->customer()->create(['available' => 1]);

        $this->actingAs($actor)->post(route('manager.users.update'), [
            'slack' => $target->slack,
            'firstname' => $target->firstname,
            'lastname' => $target->lastname,
            'email' => $target->email,
            'identification' => $target->identification,
            'role' => 'manager',
            'available' => '0',
        ])->assertOk()->assertJson(['success' => true]);

        Event::assertDispatched(UserDeactivated::class);
        Event::assertNotDispatched(UserReactivated::class);
    }

    public function test_update_dispatches_user_reactivated_when_available_flips_to_one(): void
    {
        Event::fake([UserDeactivated::class, UserReactivated::class]);
        $actor = User::factory()->manager()->create();
        $target = User::factory()->customer()->create(['available' => 0]);

        $this->actingAs($actor)->post(route('manager.users.update'), [
            'slack' => $target->slack,
            'firstname' => $target->firstname,
            'lastname' => $target->lastname,
            'email' => $target->email,
            'identification' => $target->identification,
            'role' => 'manager',
            'available' => '1',
        ])->assertOk()->assertJson(['success' => true]);

        Event::assertDispatched(UserReactivated::class);
        Event::assertNotDispatched(UserDeactivated::class);
    }

    public function test_destroy_dispatches_user_deleted(): void
    {
        Event::fake([UserDeleted::class]);
        $actor = User::factory()->manager()->create();
        $target = User::factory()->customer()->create();

        $this->actingAs($actor)
            ->delete(route('manager.users.destroy', $target->slack))
            ->assertRedirect(route('manager.users'));

        Event::assertDispatched(UserDeleted::class, function ($event) use ($target) {
            return $event->user->id === $target->id;
        });
    }

    public function test_update_customer_without_enterprise_selection_does_not_fail(): void
    {
        // La mayoria de clientes no pertenece a ninguna empresa: el combo
        // "Empresa" queda vacio y no debe intentar crear un EnterpriseUser
        // con enterprise_id NULL (columna NOT NULL -> 500).
        $actor = User::factory()->manager()->create();
        $customer = User::factory()->customer()->create(['address' => 'Direccion vieja']);

        $this->actingAs($actor)
            ->post(route('manager.users.update'), [
                'slack' => $customer->slack,
                'firstname' => $customer->firstname,
                'lastname' => $customer->lastname,
                'email' => $customer->email,
                'identification' => $customer->identification,
                'role' => 'customer',
                'enterprises' => '',
                'address' => 'Direccion nueva',
                'available' => '1',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $customer->refresh();
        $this->assertSame('Direccion nueva', $customer->address);
        $this->assertNull($customer->relation);
    }

    // ── Regresión: store()/update() leían `$request->enterprise` (singular) ──
    // ── para el rol 'enterprise', pero el <select> real del form (create y ───
    // ── edit blade) se llama `enterprises` (plural, mismo campo que usa el ───
    // ── rol 'customer') -- la empresa seleccionada nunca se guardaba, ────────
    // ── enterprise_id quedaba siempre NULL. Ver tambien los typos 'enterprise'
    // ── vs 'enterprises' en create.js/edit.js que ademas ocultaban el campo.

    public function test_store_with_enterprise_role_saves_selected_enterprise_id(): void
    {
        $actor = User::factory()->manager()->create();
        $enterprise = Enterprise::create([
            'slack' => (string) Str::uuid(),
            'title' => 'QA Empresa Test',
            'available' => 1,
        ]);

        $this->actingAs($actor)->post(route('manager.users.store'), [
            'firstname' => 'QA',
            'lastname' => 'Enterprise',
            'email' => 'qa-enterprise-store@example.com',
            'role' => 'enterprise',
            'password' => 'password123',
            'enterprises' => (string) $enterprise->id,
        ])->assertOk()->assertJson(['success' => true]);

        $created = User::where('email', 'qa-enterprise-store@example.com')->first();
        $this->assertSame($enterprise->id, $created->enterprise_id);
    }

    public function test_update_with_enterprise_role_saves_selected_enterprise_id(): void
    {
        $actor = User::factory()->manager()->create();
        $target = User::factory()->create(['role' => 'enterprise', 'enterprise_id' => null]);
        $enterprise = Enterprise::create([
            'slack' => (string) Str::uuid(),
            'title' => 'QA Empresa Test 2',
            'available' => 1,
        ]);

        $this->actingAs($actor)->post(route('manager.users.update'), [
            'slack' => $target->slack,
            'firstname' => $target->firstname,
            'lastname' => $target->lastname,
            'email' => $target->email,
            'identification' => $target->identification,
            'role' => 'enterprise',
            'available' => '1',
            'enterprises' => (string) $enterprise->id,
        ])->assertOk()->assertJson(['success' => true]);

        $this->assertSame($enterprise->id, $target->fresh()->enterprise_id);
    }
}
