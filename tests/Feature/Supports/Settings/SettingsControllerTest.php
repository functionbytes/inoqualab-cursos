<?php

namespace Tests\Feature\Supports\Settings;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_update_profile_does_not_require_the_support_field(): void
    {
        // El campo "support" (nombre de soporte) no se usa en ningun otro
        // punto del sistema y los soportes reales de la BD nunca lo tuvieron
        // poblado -- marcado required bloqueaba editar el propio perfil
        // (incluso solo el password) para el 100% de ellos.
        $support = User::factory()->create(['role' => 'support', 'support' => null]);

        $this->actingAs($support)
            ->post(route('support.settings.profile.update'), [
                'firstname' => $support->firstname,
                'lastname' => $support->lastname,
                'email' => $support->email,
                'support' => '',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertNull($support->fresh()->support);
    }

    public function test_update_profile_still_saves_the_support_field_when_provided(): void
    {
        $support = User::factory()->create(['role' => 'support']);

        $this->actingAs($support)
            ->post(route('support.settings.profile.update'), [
                'firstname' => $support->firstname,
                'lastname' => $support->lastname,
                'email' => $support->email,
                'support' => 'mesa de ayuda',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame('MESA DE AYUDA', $support->fresh()->support);
    }
}
