<?php

namespace Tests\Feature\Managers\Seo;

use App\Models\Seo\SeoAlert;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SeoAlertBulkActionTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->manager = User::factory()->manager()->create();
    }

    protected function makeAlert(array $overrides = []): SeoAlert
    {
        return SeoAlert::create(array_merge([
            'type' => SeoAlert::TYPE_NEW_404,
            'severity' => SeoAlert::SEVERITY_WARNING,
            'title' => 'Alerta de prueba',
        ], $overrides));
    }

    public function test_bulk_acknowledge_sets_acknowledged_fields(): void
    {
        $alert = $this->makeAlert();

        $this->actingAs($this->manager)
            ->postJson(route('manager.seo.alerts.bulk-action'), ['action' => 'acknowledge', 'ids' => [$alert->id]])
            ->assertOk()
            ->assertJson(['success' => true, 'message' => '1 alerta(s) procesadas.']);

        $alert->refresh();
        $this->assertNotNull($alert->acknowledged_at);
        $this->assertSame($this->manager->id, $alert->acknowledged_by);
    }

    public function test_bulk_delete_removes_alerts(): void
    {
        $a = $this->makeAlert();
        $b = $this->makeAlert();

        $this->actingAs($this->manager)
            ->postJson(route('manager.seo.alerts.bulk-action'), ['action' => 'delete', 'ids' => [$a->id, $b->id]])
            ->assertOk();

        $this->assertDatabaseCount('seo_alerts', 0);
    }

    public function test_bulk_action_rejects_invalid_action(): void
    {
        $alert = $this->makeAlert();

        $this->actingAs($this->manager)
            ->postJson(route('manager.seo.alerts.bulk-action'), ['action' => 'archive', 'ids' => [$alert->id]])
            ->assertUnprocessable();

        $this->assertDatabaseCount('seo_alerts', 1);
    }

    public function test_bulk_action_rejects_missing_ids(): void
    {
        $this->actingAs($this->manager)
            ->postJson(route('manager.seo.alerts.bulk-action'), ['action' => 'delete'])
            ->assertUnprocessable();
    }

    public function test_bulk_action_forbidden_without_seo_delete_permission(): void
    {
        $alert = $this->makeAlert();
        Role::findByName('manager')->revokePermissionTo('seo.delete');

        $this->actingAs(User::factory()->manager()->create())
            ->postJson(route('manager.seo.alerts.bulk-action'), ['action' => 'acknowledge', 'ids' => [$alert->id]])
            ->assertForbidden();

        $this->assertNull($alert->fresh()->acknowledged_at);
    }
}
