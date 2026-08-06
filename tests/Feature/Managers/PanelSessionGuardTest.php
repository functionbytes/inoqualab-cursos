<?php

namespace Tests\Feature\Managers;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresión: routes/managers.php era el único de los 5 portales protegidos
 * SIN el middleware `session` (CheckSession) -- accountings/customers/
 * distributors/enterprises/supports sí lo tienen. Sin él, en /panel/*:
 *
 * 1. Una cuenta manager deshabilitada (available=0) seguía con sesión activa
 *    indefinidamente -- nada llamaba Auth::logout().
 * 2. El mecanismo "última sesión gana" (otro login desplaza a los demás
 *    dispositivos) tampoco aplicaba al panel de mayor privilegio del sistema.
 */
class PanelSessionGuardTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->manager = User::factory()->manager()->create(['available' => 1]);
    }

    public function test_disabled_manager_account_is_logged_out_of_the_panel(): void
    {
        $this->manager->available = 0;
        $this->manager->save();

        $this->actingAs($this->manager)
            ->get(route('manager.dashboard'))
            ->assertRedirect(route('session.expired'));
    }

    public function test_manager_session_superseded_by_another_login_is_logged_out(): void
    {
        $this->manager->session = 'otra-sesion-de-otro-dispositivo';
        $this->manager->save();

        $this->actingAs($this->manager)
            ->get(route('manager.dashboard'))
            ->assertRedirect(route('session.expired', ['reason' => 'device']));
    }

    public function test_active_manager_with_no_conflicting_session_can_access_the_panel(): void
    {
        $this->actingAs($this->manager)
            ->get(route('manager.dashboard'))
            ->assertOk();
    }
}
