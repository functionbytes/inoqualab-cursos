<?php

namespace Tests\Feature\Managers\Seo;

use App\Models\Seo\SeoRedirect;
use App\Models\Seo\SeoTemplate;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cobertura de escritura del módulo SEO (redirects y templates), incluyendo
 * la regresión del bulk-delete de templates que usaba `->each->delete()` sobre
 * un Builder (causaba 500).
 */
class SeoManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->manager = User::factory()->manager()->create();
    }

    // ── Redirects ─────────────────────────────────────────────────────────

    public function test_manager_can_create_redirect(): void
    {
        $this->actingAs($this->manager)
            ->postJson(route('manager.seo.redirects.store'), [
                'source_path' => '/old',
                'target_path' => '/new',
                'status_code' => 301,
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseCount('seo_redirects', 1);
        $this->assertDatabaseHas('seo_redirects', ['status_code' => 301]);
    }

    public function test_redirect_rejects_invalid_status_code(): void
    {
        $this->actingAs($this->manager)
            ->postJson(route('manager.seo.redirects.store'), [
                'source_path' => '/old',
                'target_path' => '/new',
                'status_code' => 307,
            ])
            ->assertUnprocessable();

        $this->assertDatabaseCount('seo_redirects', 0);
    }

    public function test_manager_can_update_redirect(): void
    {
        $redirect = SeoRedirect::create([
            'source_path' => '/a', 'target_path' => '/b', 'status_code' => 301, 'is_active' => true,
        ]);

        $this->actingAs($this->manager)
            ->putJson(route('manager.seo.redirects.update', $redirect), [
                'source_path' => '/a', 'target_path' => '/c', 'status_code' => 302,
            ])
            ->assertOk();

        $this->assertSame(302, $redirect->fresh()->status_code);
    }

    public function test_manager_can_toggle_and_delete_redirect(): void
    {
        $redirect = SeoRedirect::create([
            'source_path' => '/x', 'target_path' => '/y', 'status_code' => 301, 'is_active' => true,
        ]);

        $this->actingAs($this->manager)
            ->postJson(route('manager.seo.redirects.toggle', $redirect))
            ->assertOk();
        $this->assertFalse($redirect->fresh()->is_active);

        $this->actingAs($this->manager)
            ->deleteJson(route('manager.seo.redirects.destroy', $redirect))
            ->assertOk();
        $this->assertDatabaseMissing('seo_redirects', ['id' => $redirect->id]);
    }

    public function test_manager_can_bulk_destroy_redirects(): void
    {
        $a = SeoRedirect::create(['source_path' => '/1', 'target_path' => '/1b', 'status_code' => 301]);
        $b = SeoRedirect::create(['source_path' => '/2', 'target_path' => '/2b', 'status_code' => 301]);

        $this->actingAs($this->manager)
            ->postJson(route('manager.seo.redirects.bulk-destroy'), ['ids' => [$a->id, $b->id]])
            ->assertOk();

        $this->assertDatabaseCount('seo_redirects', 0);
    }

    // ── Templates ─────────────────────────────────────────────────────────

    public function test_manager_can_create_template(): void
    {
        $this->actingAs($this->manager)
            ->post(route('manager.seo.templates.store'), [
                'name' => 'Cursos', 'priority' => 5, 'is_active' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('seo_templates', ['name' => 'Cursos', 'priority' => 5]);
    }

    public function test_manager_can_bulk_delete_templates(): void
    {
        // Regresión: el bulk delete usaba ->each->delete() sobre un Builder (500).
        $a = SeoTemplate::create(['name' => 'A', 'is_active' => true, 'priority' => 1]);
        $b = SeoTemplate::create(['name' => 'B', 'is_active' => true, 'priority' => 1]);

        $this->actingAs($this->manager)
            ->postJson(route('manager.seo.templates.bulk-action'), ['action' => 'delete', 'ids' => [$a->id, $b->id]])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseCount('seo_templates', 0);
    }

    public function test_manager_can_bulk_activate_templates(): void
    {
        $a = SeoTemplate::create(['name' => 'A', 'is_active' => false, 'priority' => 1]);

        $this->actingAs($this->manager)
            ->postJson(route('manager.seo.templates.bulk-action'), ['action' => 'activate', 'ids' => [$a->id]])
            ->assertOk();

        $this->assertTrue($a->fresh()->is_active);
    }

    public function test_manager_can_bulk_deactivate_templates(): void
    {
        $a = SeoTemplate::create(['name' => 'A', 'is_active' => true, 'priority' => 1]);

        $this->actingAs($this->manager)
            ->postJson(route('manager.seo.templates.bulk-action'), ['action' => 'deactivate', 'ids' => [$a->id]])
            ->assertOk();

        $this->assertFalse($a->fresh()->is_active);
    }

    public function test_bulk_action_rejects_invalid_action_on_templates(): void
    {
        $a = SeoTemplate::create(['name' => 'A', 'is_active' => true, 'priority' => 1]);

        $this->actingAs($this->manager)
            ->postJson(route('manager.seo.templates.bulk-action'), ['action' => 'archive', 'ids' => [$a->id]])
            ->assertUnprocessable();

        $this->assertDatabaseCount('seo_templates', 1);
    }
}
