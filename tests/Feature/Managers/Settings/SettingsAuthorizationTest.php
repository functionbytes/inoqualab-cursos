<?php

namespace Tests\Feature\Managers\Settings;

use App\Models\Contact;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Defensa en profundidad: ContactsController::index/edit y
 * SettingsController (logo/favicon) deben rechazar explícitamente a un
 * usuario sin el permiso correspondiente, sin depender solo del middleware
 * de convención `panel.permission`.
 */
class SettingsAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function userWithoutPermissions(): User
    {
        // role=manager para pasar el middleware IsManager (columna directa) y
        // llegar al controller; sin permisos Spatie para probar el abort_unless
        // interno. Con role=customer, IsManager redirige (302) antes de llegar
        // ahí y el test nunca ejercita el fix real.
        $user = User::factory()->manager()->create();
        $user->syncRoles([]);
        $user->syncPermissions([]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $user;
    }

    private function contact(): Contact
    {
        return Contact::create([
            'slack' => (string) Str::uuid(),
            'firstname' => 'Juan',
            'lastname' => 'Perez',
            'email' => 'juan@example.com',
            'cellphone' => '3000000000',
            'message' => 'Mensaje de prueba',
            'reviewed' => 0,
        ]);
    }

    public function test_user_without_permission_cannot_list_contacts(): void
    {
        $user = $this->userWithoutPermissions();

        $this->actingAs($user)
            ->get(route('manager.contacts'))
            ->assertForbidden();
    }

    public function test_user_without_permission_cannot_edit_contact(): void
    {
        $user = $this->userWithoutPermissions();
        $contact = $this->contact();

        $this->actingAs($user)
            ->get(route('manager.contacts.edit', $contact->slack))
            ->assertForbidden();
    }

    public function test_user_without_permission_cannot_view_general_settings(): void
    {
        $user = $this->userWithoutPermissions();

        $this->actingAs($user)
            ->get(route('manager.settings'))
            ->assertForbidden();
    }

    public function test_user_without_permission_cannot_store_logo(): void
    {
        $user = $this->userWithoutPermissions();

        $this->actingAs($user)
            ->post(route('manager.settings.logo'), [])
            ->assertForbidden();
    }

    public function test_user_without_permission_cannot_store_favicon(): void
    {
        $user = $this->userWithoutPermissions();

        $this->actingAs($user)
            ->post(route('manager.settings.favicon'), [])
            ->assertForbidden();
    }

    public function test_user_without_permission_cannot_get_logo(): void
    {
        $user = $this->userWithoutPermissions();

        $this->actingAs($user)
            ->get(route('manager.settings.logo.get', 'page_logo'))
            ->assertForbidden();
    }

    public function test_user_without_permission_cannot_get_favicon(): void
    {
        $user = $this->userWithoutPermissions();

        $this->actingAs($user)
            ->get(route('manager.settings.favicon.get', 'page_favicon'))
            ->assertForbidden();
    }
}
