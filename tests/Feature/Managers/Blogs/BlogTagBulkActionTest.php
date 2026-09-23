<?php

namespace Tests\Feature\Managers\Blogs;

use App\Models\Blog\BlogTag;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BlogTagBulkActionTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->manager = User::factory()->manager()->create();
    }

    protected function makeTag(array $overrides = []): BlogTag
    {
        $title = $overrides['title'] ?? 'Etiqueta '.Str::random(6);

        return BlogTag::create(array_merge([
            'slack' => Str::random(10),
            'title' => $title,
            'slug' => Str::slug($title),
            'available' => 1,
        ], $overrides));
    }

    public function test_bulk_publish_marks_tags_available(): void
    {
        $tag = $this->makeTag(['available' => 0]);

        $this->actingAs($this->manager)
            ->postJson(route('manager.blogs.tags.bulk-action'), ['action' => 'publish', 'ids' => [$tag->id]])
            ->assertOk();

        $this->assertSame(1, $tag->fresh()->available);
    }

    public function test_bulk_hide_marks_tags_unavailable(): void
    {
        $tag = $this->makeTag(['available' => 1]);

        $this->actingAs($this->manager)
            ->postJson(route('manager.blogs.tags.bulk-action'), ['action' => 'hide', 'ids' => [$tag->id]])
            ->assertOk();

        $this->assertSame(0, $tag->fresh()->available);
    }

    public function test_bulk_delete_removes_tags(): void
    {
        $a = $this->makeTag();
        $b = $this->makeTag();

        $this->actingAs($this->manager)
            ->postJson(route('manager.blogs.tags.bulk-action'), ['action' => 'delete', 'ids' => [$a->id, $b->id]])
            ->assertOk()
            ->assertJson(['success' => true, 'message' => '2 etiqueta(s) procesadas.']);

        $this->assertSoftDeleted('blog_tags', ['id' => $a->id]);
        $this->assertSoftDeleted('blog_tags', ['id' => $b->id]);
    }

    public function test_bulk_action_rejects_invalid_action(): void
    {
        $tag = $this->makeTag();

        $this->actingAs($this->manager)
            ->postJson(route('manager.blogs.tags.bulk-action'), ['action' => 'archive', 'ids' => [$tag->id]])
            ->assertUnprocessable();

        $this->assertDatabaseCount('blog_tags', 1);
    }

    public function test_bulk_delete_forbidden_without_delete_permission(): void
    {
        $tag = $this->makeTag();
        Role::findByName('manager')->revokePermissionTo('blogs.delete');

        $this->actingAs(User::factory()->manager()->create())
            ->postJson(route('manager.blogs.tags.bulk-action'), ['action' => 'delete', 'ids' => [$tag->id]])
            ->assertForbidden();

        $this->assertDatabaseCount('blog_tags', 1);
    }
}
