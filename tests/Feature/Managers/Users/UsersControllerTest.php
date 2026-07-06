<?php

namespace Tests\Feature\Managers\Users;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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

        $this->actingAs($actor)->post(route('manager.users.update'), [
            'slack' => $target->slack,
            'firstname' => $target->firstname,
            'lastname' => $target->lastname,
            'email' => $target->email,
            'identification' => $target->identification,
            'role' => 'customer',
            'available' => '1',
            'password' => $newPassword,
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
}
