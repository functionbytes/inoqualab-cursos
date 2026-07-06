<?php

namespace Tests\Feature\Managers\Blogs;

use App\Models\Blog\Blog;
use App\Models\Blog\BlogCategorie;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BlogAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected BlogCategorie $categorie;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->categorie = BlogCategorie::factory()->create();
    }

    public function test_store_forbidden_without_create_permission(): void
    {
        Role::findByName('manager')->revokePermissionTo('blogs.create');
        $manager = User::factory()->manager()->create();

        $this->actingAs($manager)
            ->postJson(route('manager.blogs.store'), [
                'title' => 'Intento no autorizado',
                'categorie' => $this->categorie->id,
                'available' => 1,
                'date' => now()->format('Y-m-d'),
                'contents' => '<p>Contenido</p>',
                'description' => 'Descripcion',
            ])
            ->assertForbidden();
    }

    public function test_update_forbidden_without_update_permission(): void
    {
        $blog = Blog::factory()->create(['categorie_id' => $this->categorie->id]);

        Role::findByName('manager')->revokePermissionTo('blogs.update');
        $manager = User::factory()->manager()->create();

        $this->actingAs($manager)
            ->postJson(route('manager.blogs.update'), [
                'slack' => $blog->slack,
                'title' => 'Intento no autorizado',
                'categorie' => $this->categorie->id,
                'available' => 1,
                'date' => now()->format('Y-m-d'),
                'contents' => '<p>Contenido</p>',
                'description' => 'Descripcion',
            ])
            ->assertForbidden();
    }

    public function test_destroy_forbidden_without_delete_permission(): void
    {
        $blog = Blog::factory()->create(['categorie_id' => $this->categorie->id]);

        Role::findByName('manager')->revokePermissionTo('blogs.delete');
        $manager = User::factory()->manager()->create();

        $this->actingAs($manager)
            ->delete(route('manager.blogs.destroy', $blog->slack))
            ->assertForbidden();
    }
}
