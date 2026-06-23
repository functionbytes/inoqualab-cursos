<?php

namespace Tests\Feature\Managers;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cobertura del visor de audit-log (Spatie activitylog) en el panel manager:
 * que la ruta exista, el permiso `activity.view` esté cableado y los filtros
 * (whereHasMorph causer, rango de fechas) no produzcan errores.
 */
class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_manager_can_view_activity_log(): void
    {
        $manager = User::factory()->manager()->create();

        $this->actingAs($manager)
            ->get(route('manager.activity.index'))
            ->assertOk()
            ->assertSee('Registro de actividad');
    }

    public function test_activity_log_filters_do_not_error(): void
    {
        $manager = User::factory()->manager()->create();

        $this->actingAs($manager)
            ->get(route('manager.activity.index', [
                'event' => 'updated',
                'subject_type' => User::class,
                'causer' => 'test',
                'date_from' => '2026-01-01',
                'date_to' => '2026-12-31',
            ]))
            ->assertOk();
    }
}
