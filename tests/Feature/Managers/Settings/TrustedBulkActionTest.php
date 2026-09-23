<?php

namespace Tests\Feature\Managers\Settings;

use App\Models\Trusted;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TrustedBulkActionTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->manager = User::factory()->manager()->create();
    }

    protected function makeTrusted(array $overrides = []): Trusted
    {
        $title = $overrides['title'] ?? 'Aliado '.Str::random(6);

        return Trusted::create(array_merge([
            'slack' => Str::random(10),
            'title' => $title,
            'slug' => Str::slug($title),
            'url' => '/destino',
            'available' => 1,
        ], $overrides));
    }

    public function test_bulk_publish_marks_trusteds_available(): void
    {
        $trusted = $this->makeTrusted(['available' => 0]);

        $this->actingAs($this->manager)
            ->postJson(route('manager.trusteds.bulk-action'), ['action' => 'publish', 'ids' => [$trusted->id]])
            ->assertOk();

        $this->assertSame(1, $trusted->fresh()->available);
    }

    public function test_bulk_hide_marks_trusteds_unavailable(): void
    {
        $trusted = $this->makeTrusted(['available' => 1]);

        $this->actingAs($this->manager)
            ->postJson(route('manager.trusteds.bulk-action'), ['action' => 'hide', 'ids' => [$trusted->id]])
            ->assertOk();

        $this->assertSame(0, $trusted->fresh()->available);
    }

    public function test_bulk_delete_removes_trusteds(): void
    {
        $a = $this->makeTrusted();
        $b = $this->makeTrusted();

        $this->actingAs($this->manager)
            ->postJson(route('manager.trusteds.bulk-action'), ['action' => 'delete', 'ids' => [$a->id, $b->id]])
            ->assertOk()
            ->assertJson(['success' => true, 'message' => '2 aliado(s) procesados.']);

        $this->assertDatabaseCount('trusteds', 0);
    }

    public function test_bulk_action_rejects_invalid_action(): void
    {
        $trusted = $this->makeTrusted();

        $this->actingAs($this->manager)
            ->postJson(route('manager.trusteds.bulk-action'), ['action' => 'archive', 'ids' => [$trusted->id]])
            ->assertUnprocessable();

        $this->assertDatabaseCount('trusteds', 1);
    }

    public function test_bulk_delete_forbidden_without_delete_permission(): void
    {
        $trusted = $this->makeTrusted();
        Role::findByName('manager')->revokePermissionTo('trusteds.delete');

        $this->actingAs(User::factory()->manager()->create())
            ->postJson(route('manager.trusteds.bulk-action'), ['action' => 'delete', 'ids' => [$trusted->id]])
            ->assertForbidden();

        $this->assertDatabaseCount('trusteds', 1);
    }
}
