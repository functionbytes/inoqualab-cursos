<?php

namespace Tests\Feature\Managers\Blogs;

use App\Models\Blog\BlogCategorie;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BlogCategoryBulkActionTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->manager = User::factory()->manager()->create();
    }

    public function test_bulk_publish_marks_categories_available(): void
    {
        $categorie = BlogCategorie::factory()->unavailable()->create();

        $this->actingAs($this->manager)
            ->postJson(route('manager.blogs.categories.bulk-action'), ['action' => 'publish', 'ids' => [$categorie->id]])
            ->assertOk();

        $this->assertSame(1, $categorie->fresh()->available);
    }

    public function test_bulk_hide_marks_categories_unavailable(): void
    {
        $categorie = BlogCategorie::factory()->create(['available' => 1]);

        $this->actingAs($this->manager)
            ->postJson(route('manager.blogs.categories.bulk-action'), ['action' => 'hide', 'ids' => [$categorie->id]])
            ->assertOk();

        $this->assertSame(0, $categorie->fresh()->available);
    }

    public function test_bulk_delete_removes_categories(): void
    {
        $a = BlogCategorie::factory()->create();
        $b = BlogCategorie::factory()->create();

        $this->actingAs($this->manager)
            ->postJson(route('manager.blogs.categories.bulk-action'), ['action' => 'delete', 'ids' => [$a->id, $b->id]])
            ->assertOk()
            ->assertJson(['success' => true, 'message' => '2 categoria(s) procesadas.']);

        $this->assertSoftDeleted('blog_categories', ['id' => $a->id]);
        $this->assertSoftDeleted('blog_categories', ['id' => $b->id]);
    }

    public function test_bulk_action_rejects_invalid_action(): void
    {
        $categorie = BlogCategorie::factory()->create();

        $this->actingAs($this->manager)
            ->postJson(route('manager.blogs.categories.bulk-action'), ['action' => 'archive', 'ids' => [$categorie->id]])
            ->assertUnprocessable();

        $this->assertDatabaseCount('blog_categories', 1);
    }

    public function test_bulk_delete_forbidden_without_delete_permission(): void
    {
        $categorie = BlogCategorie::factory()->create();
        Role::findByName('manager')->revokePermissionTo('blogs.delete');

        $this->actingAs(User::factory()->manager()->create())
            ->postJson(route('manager.blogs.categories.bulk-action'), ['action' => 'delete', 'ids' => [$categorie->id]])
            ->assertForbidden();

        $this->assertDatabaseCount('blog_categories', 1);
    }
}
