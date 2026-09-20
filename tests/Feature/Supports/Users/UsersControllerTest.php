<?php

namespace Tests\Feature\Supports\Users;

use App\Models\Enterprise\Enterprise;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UsersControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $support;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->support = User::factory()->support()->create();
    }

    public function test_index_lists_users(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($this->support)
            ->get(route('support.users'))
            ->assertOk()
            ->assertSee($customer->email);
    }

    public function test_create_page_loads(): void
    {
        $this->actingAs($this->support)
            ->get(route('support.users.create'))
            ->assertOk();
    }

    public function test_store_creates_a_customer(): void
    {
        $response = $this->actingAs($this->support)
            ->post(route('support.users.store'), [
                'firstname' => 'Prueba',
                'lastname' => 'QADiagnostico',
                'email' => 'prueba.qadiagnostico@example.test',
                'identification' => '999888777',
                'cellphone' => '3001234567',
                'role' => 'customer',
                'password' => 'password123',
            ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('users', [
            'email' => 'prueba.qadiagnostico@example.test',
            'role' => 'customer',
        ]);
    }

    public function test_view_page_loads_for_manageable_role(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($this->support)
            ->get(route('support.users.view', $customer->slack))
            ->assertOk()
            ->assertSee($customer->firstname);
    }

    public function test_edit_page_loads_for_manageable_role(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($this->support)
            ->get(route('support.users.edit', $customer->slack))
            ->assertOk()
            ->assertSee($customer->email);
    }

    public function test_navegation_hub_loads(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($this->support)
            ->get(route('support.users.navegation', $customer->slack))
            ->assertOk();
    }

    public function test_support_cannot_view_a_manager_account(): void
    {
        $target = User::factory()->manager()->create();

        $this->actingAs($this->support)
            ->get(route('support.users.view', $target->slack))
            ->assertForbidden();
    }

    public function test_support_cannot_destroy_a_manager_account(): void
    {
        $target = User::factory()->manager()->create();

        $this->actingAs($this->support)
            ->delete(route('support.users.destroy', $target->slack));

        $this->assertNotNull($target->fresh());
    }

    public function test_destroy_removes_a_manageable_user(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($this->support)
            ->delete(route('support.users.destroy', $customer->slack));

        $this->assertNull(User::find($customer->id));
    }

    public function test_information_updates_basic_profile_fields(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($this->support)
            ->post(route('support.users.information'), [
                'slack' => $customer->slack,
                'firstname' => 'Actualizado',
                'lastname' => $customer->lastname,
                'cellphone' => $customer->cellphone,
                'email' => $customer->email,
                'address' => $customer->address,
                'role' => 'customer',
                'available' => 1,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame('ACTUALIZADO', $customer->fresh()->firstname);
    }

    public function test_information_rejects_role_escalation_to_manager(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($this->support)
            ->post(route('support.users.information'), [
                'slack' => $customer->slack,
                'firstname' => $customer->firstname,
                'lastname' => $customer->lastname,
                'email' => $customer->email,
                'role' => 'manager',
                'available' => 1,
            ])
            ->assertForbidden();

        $this->assertSame('customer', $customer->fresh()->role);
    }

    public function test_resetpassword_requires_matching_confirmation(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($this->support)
            ->post(route('support.users.resetpassword'), [
                'slack' => $customer->slack,
                'new_password' => 'password123',
                'new_password_confirmation' => 'different123',
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_resetpassword_updates_password_for_manageable_user(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($this->support)
            ->post(route('support.users.resetpassword'), [
                'slack' => $customer->slack,
                'new_password' => 'newPassword123',
                'new_password_confirmation' => 'newPassword123',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertTrue(Hash::check('newPassword123', $customer->fresh()->password));
    }

    public function test_notification_updates_notification_flags(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($this->support)
            ->post(route('support.users.notification'), [
                'slack' => $customer->slack,
                'newsletter_notification' => 'true',
                'order_notification' => 'false',
                'status_notification' => 'true',
                'email_notification' => 'true',
                'cookies_notification' => 'false',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $customer->refresh();
        $this->assertSame(1, (int) $customer->newsletter_notification);
        $this->assertSame(0, (int) $customer->order_notification);
    }

    // ── Bug: notification() era el único mutador del controller sin el guard ──
    // ── guardManageableUser() -- un support podía tocar notificaciones de un ──
    // ── manager/support sin verificación de rol ────────────────────────────

    public function test_support_cannot_change_notification_preferences_of_a_manager_account(): void
    {
        $target = User::factory()->manager()->create();

        $this->actingAs($this->support)
            ->post(route('support.users.notification'), [
                'slack' => $target->slack,
                'newsletter_notification' => 'true',
            ])
            ->assertForbidden();

        $this->assertSame(0, (int) $target->fresh()->newsletter_notification);
    }

    // ── Bug: store()/update() leían $request->enterprise (singular) pero el ──
    // ── <select id="enterprises"> del formulario envía 'enterprises' (plural) ──

    public function test_store_sets_enterprise_id_for_an_enterprise_role_user(): void
    {
        $enterprise = Enterprise::factory()->create();

        $response = $this->actingAs($this->support)
            ->post(route('support.users.store'), [
                'firstname' => 'Staff',
                'lastname' => 'Empresa',
                'email' => 'staff.empresa@example.test',
                'identification' => '111222333',
                'role' => 'enterprise',
                'enterprises' => $enterprise->id,
                'password' => 'password123',
            ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('users', [
            'email' => 'staff.empresa@example.test',
            'role' => 'enterprise',
            'enterprise_id' => $enterprise->id,
        ]);
    }

    public function test_update_sets_enterprise_id_for_an_enterprise_role_user(): void
    {
        $enterprise = Enterprise::factory()->create();
        $staff = User::factory()->create(['role' => 'enterprise', 'enterprise_id' => null]);

        $this->actingAs($this->support)
            ->post(route('support.users.update'), [
                'slack' => $staff->slack,
                'firstname' => $staff->firstname,
                'lastname' => $staff->lastname,
                'email' => $staff->email,
                'identification' => $staff->identification,
                'role' => 'enterprise',
                'enterprises' => $enterprise->id,
                'available' => 1,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame($enterprise->id, $staff->fresh()->enterprise_id);
    }

    public function test_update_customer_without_enterprise_selection_does_not_fail(): void
    {
        // La mayoria de clientes no pertenece a ninguna empresa: el combo
        // "Empresa" queda vacio y no debe intentar crear un EnterpriseUser
        // con enterprise_id NULL (columna NOT NULL -> 500).
        $customer = User::factory()->customer()->create(['address' => 'Direccion vieja']);

        $this->actingAs($this->support)
            ->post(route('support.users.update'), [
                'slack' => $customer->slack,
                'firstname' => $customer->firstname,
                'lastname' => $customer->lastname,
                'email' => $customer->email,
                'identification' => $customer->identification,
                'role' => 'customer',
                'enterprises' => '',
                'address' => 'Direccion nueva',
                'available' => 1,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $customer->refresh();
        $this->assertSame('Direccion nueva', $customer->address);
        $this->assertNull($customer->relation);
    }
}
