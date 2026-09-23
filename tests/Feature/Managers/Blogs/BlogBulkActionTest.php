<?php

namespace Tests\Feature\Managers\Blogs;

use App\Models\Blog\Blog;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BlogBulkActionTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->manager = User::factory()->manager()->create();
    }

    public function test_bulk_publish_marks_blogs_available(): void
    {
        $a = Blog::factory()->unavailable()->create();
        $b = Blog::factory()->unavailable()->create();

        $this->actingAs($this->manager)
            ->postJson(route('manager.blogs.bulk-action'), ['action' => 'publish', 'ids' => [$a->id, $b->id]])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame(1, $a->fresh()->available);
        $this->assertSame(1, $b->fresh()->available);
    }

    public function test_bulk_hide_marks_blogs_unavailable(): void
    {
        $blog = Blog::factory()->create(['available' => 1]);

        $this->actingAs($this->manager)
            ->postJson(route('manager.blogs.bulk-action'), ['action' => 'hide', 'ids' => [$blog->id]])
            ->assertOk();

        $this->assertSame(0, $blog->fresh()->available);
    }

    public function test_bulk_delete_removes_blogs(): void
    {
        $a = Blog::factory()->create();
        $b = Blog::factory()->create();

        $this->actingAs($this->manager)
            ->postJson(route('manager.blogs.bulk-action'), ['action' => 'delete', 'ids' => [$a->id, $b->id]])
            ->assertOk()
            ->assertJson(['success' => true, 'message' => '2 noticia(s) procesadas.']);

        $this->assertSoftDeleted('blogs', ['id' => $a->id]);
        $this->assertSoftDeleted('blogs', ['id' => $b->id]);
    }

    public function test_bulk_action_rejects_invalid_action(): void
    {
        $blog = Blog::factory()->create();

        $this->actingAs($this->manager)
            ->postJson(route('manager.blogs.bulk-action'), ['action' => 'archive', 'ids' => [$blog->id]])
            ->assertUnprocessable();

        $this->assertDatabaseCount('blogs', 1);
    }

    public function test_bulk_action_rejects_missing_ids(): void
    {
        $this->actingAs($this->manager)
            ->postJson(route('manager.blogs.bulk-action'), ['action' => 'delete'])
            ->assertUnprocessable();
    }

    public function test_bulk_delete_forbidden_without_delete_permission(): void
    {
        $blog = Blog::factory()->create();
        Role::findByName('manager')->revokePermissionTo('blogs.delete');

        $this->actingAs(User::factory()->manager()->create())
            ->postJson(route('manager.blogs.bulk-action'), ['action' => 'delete', 'ids' => [$blog->id]])
            ->assertForbidden();

        $this->assertDatabaseCount('blogs', 1);
    }

    public function test_bulk_publish_forbidden_without_update_permission(): void
    {
        $blog = Blog::factory()->unavailable()->create();
        Role::findByName('manager')->revokePermissionTo('blogs.update');

        $this->actingAs(User::factory()->manager()->create())
            ->postJson(route('manager.blogs.bulk-action'), ['action' => 'publish', 'ids' => [$blog->id]])
            ->assertForbidden();

        $this->assertSame(0, $blog->fresh()->available);
    }
}
