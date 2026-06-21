<?php

namespace Tests\Feature\Managers\Analytics;

use App\Models\AnalyticsReportSchedule;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Cobertura de los reportes programados de Analytics. Verifica el CRUD y que
 * la escritura exige permiso `analytics.*` (el StoreScheduleRequest autorizaba
 * con `true` y el grupo de rutas derivaba `settings.create`, inexistente).
 */
class AnalyticsScheduleTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->manager = User::factory()->manager()->create();
    }

    private function viewer(): User
    {
        $viewer = User::factory()->manager()->create();
        $viewer->syncRoles([]);
        $viewer->givePermissionTo('analytics.view');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $viewer->fresh();
    }

    private function schedule(): AnalyticsReportSchedule
    {
        return AnalyticsReportSchedule::create([
            'name' => 'Reporte semanal',
            'frequency' => 'weekly',
            'email' => 'reportes@example.com',
            'format' => 'pdf',
            'is_active' => true,
            'next_run_at' => now()->addWeek(),
        ]);
    }

    public function test_manager_can_create_schedule(): void
    {
        $this->actingAs($this->manager)
            ->post(route('manager.settings.analytics.schedules.store'), [
                'name' => 'Reporte diario', 'frequency' => 'daily', 'email' => 'r@example.com', 'format' => 'excel',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('analytics_report_schedules', ['name' => 'Reporte diario', 'frequency' => 'daily']);
    }

    public function test_store_requires_write_permission(): void
    {
        // Escalada cerrada: analytics.view no basta para crear un reporte programado.
        $this->actingAs($this->viewer())
            ->post(route('manager.settings.analytics.schedules.store'), [
                'name' => 'X', 'frequency' => 'daily', 'email' => 'r@example.com', 'format' => 'pdf',
            ])
            ->assertForbidden();
    }

    public function test_destroy_requires_delete_permission(): void
    {
        $schedule = $this->schedule();

        $this->actingAs($this->viewer())
            ->delete(route('manager.settings.analytics.schedules.destroy', $schedule))
            ->assertForbidden();

        $this->assertDatabaseHas('analytics_report_schedules', ['id' => $schedule->id]);
    }

    public function test_manager_can_delete_schedule(): void
    {
        $schedule = $this->schedule();

        $this->actingAs($this->manager)
            ->delete(route('manager.settings.analytics.schedules.destroy', $schedule))
            ->assertRedirect();

        $this->assertDatabaseMissing('analytics_report_schedules', ['id' => $schedule->id]);
    }
}
