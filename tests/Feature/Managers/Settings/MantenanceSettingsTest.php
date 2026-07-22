<?php

namespace Tests\Feature\Managers\Settings;

use App\Models\Setting\Setting;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * MantenanceSettingsController: la llave de bypass de mantenimiento debe
 * generarse una sola vez (persistida), nunca viajar en texto plano en el
 * HTML de index(), y el endpoint debe estar protegido por permiso.
 */
class MantenanceSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function managerUser(): User
    {
        return User::factory()->manager()->create();
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

    public function test_secret_is_not_exposed_in_plain_text_on_index(): void
    {
        $manager = $this->managerUser();

        $response = $this->actingAs($manager)->get(route('manager.settings.maintenance'));

        $response->assertOk();

        $secret = Setting::where('key', 'maintenance_mode_value')->value('value');
        $this->assertNotNull($secret);
        $response->assertDontSee($secret);
    }

    public function test_secret_persists_between_requests_instead_of_regenerating(): void
    {
        $manager = $this->managerUser();

        $this->actingAs($manager)->get(route('manager.settings.maintenance'))->assertOk();
        $first = Setting::where('key', 'maintenance_mode_value')->value('value');

        $this->actingAs($manager)->get(route('manager.settings.maintenance'))->assertOk();
        $second = Setting::where('key', 'maintenance_mode_value')->value('value');

        $this->assertNotNull($first);
        $this->assertSame($first, $second);
    }

    public function test_secret_endpoint_returns_the_persisted_value(): void
    {
        $manager = $this->managerUser();

        $this->actingAs($manager)->get(route('manager.settings.maintenance'))->assertOk();
        $persisted = Setting::where('key', 'maintenance_mode_value')->value('value');

        $response = $this->actingAs($manager)->getJson(route('manager.settings.maintenance.secret'));

        $response->assertOk();
        $this->assertSame($persisted, $response->json('value'));
    }

    public function test_user_without_permission_cannot_view_maintenance_settings(): void
    {
        $user = $this->userWithoutPermissions();

        $this->actingAs($user)
            ->get(route('manager.settings.maintenance'))
            ->assertForbidden();
    }

    public function test_user_without_permission_cannot_reveal_secret(): void
    {
        $user = $this->userWithoutPermissions();

        $this->actingAs($user)
            ->getJson(route('manager.settings.maintenance.secret'))
            ->assertForbidden();
    }
}
