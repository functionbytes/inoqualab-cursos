<?php

namespace Tests\Feature\Managers\Seo;

use App\Models\Seo\SeoAuditLog;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SeoAuditHistoryBulkActionTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->manager = User::factory()->manager()->create();
    }

    protected function makeLog(array $overrides = []): SeoAuditLog
    {
        return SeoAuditLog::create(array_merge([
            'url' => '/pagina',
            'score' => 80,
            'grade' => 'B',
            'issues_count' => 2,
            'passed_count' => 8,
            'audited_at' => now(),
        ], $overrides));
    }

    public function test_bulk_delete_removes_audit_logs(): void
    {
        $a = $this->makeLog();
        $b = $this->makeLog();

        $this->actingAs($this->manager)
            ->postJson(route('manager.seo.audit.history.bulk-action'), ['action' => 'delete', 'ids' => [$a->id, $b->id]])
            ->assertOk()
            ->assertJson(['success' => true, 'count' => 2]);

        $this->assertDatabaseCount('seo_audit_logs', 0);
    }

    public function test_bulk_action_rejects_action_other_than_delete(): void
    {
        $log = $this->makeLog();

        $this->actingAs($this->manager)
            ->postJson(route('manager.seo.audit.history.bulk-action'), ['action' => 'archive', 'ids' => [$log->id]])
            ->assertUnprocessable();

        $this->assertDatabaseCount('seo_audit_logs', 1);
    }

    public function test_bulk_action_rejects_missing_ids(): void
    {
        $this->actingAs($this->manager)
            ->postJson(route('manager.seo.audit.history.bulk-action'), ['action' => 'delete'])
            ->assertUnprocessable();
    }

    public function test_bulk_delete_forbidden_without_seo_delete_permission(): void
    {
        $log = $this->makeLog();
        Role::findByName('manager')->revokePermissionTo('seo.delete');

        $this->actingAs(User::factory()->manager()->create())
            ->postJson(route('manager.seo.audit.history.bulk-action'), ['action' => 'delete', 'ids' => [$log->id]])
            ->assertForbidden();

        $this->assertDatabaseCount('seo_audit_logs', 1);
    }

    public function test_clear_truncates_audit_history(): void
    {
        $this->makeLog();
        $this->makeLog();

        $this->actingAs($this->manager)
            ->postJson(route('manager.seo.audit.history.clear'))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseCount('seo_audit_logs', 0);
    }
}
