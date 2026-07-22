<?php

namespace Tests\Feature\Managers\Blogs;

use App\Models\Blog\BlogCategorie;
use App\Models\Blog\BlogTag;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Verifica que crear/editar categorias y etiquetas de blog funciona de
 * principio a fin (form -> ruta -> controller -> persistencia), cubriendo
 * los flujos que BlogStoreUpdateTest/BlogValidationTest no ejercitaban
 * (category update, tag store/update).
 */
class BlogTaxonomyStoreUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->manager = User::factory()->manager()->create();
    }

    public function test_category_store_creates_category(): void
    {
        $response = $this->actingAs($this->manager)
            ->postJson(route('manager.blogs.categories.store'), [
                'title' => 'Categoria nueva',
                'available' => 1,
            ]);

        $response->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('blog_categories', [
            'title' => 'Categoria nueva',
            'slug' => 'categoria-nueva',
        ]);
    }

    public function test_category_update_edits_category(): void
    {
        $categorie = BlogCategorie::factory()->create(['title' => 'Original']);

        $response = $this->actingAs($this->manager)
            ->postJson(route('manager.blogs.categories.update'), [
                'slack' => $categorie->slack,
                'title' => 'Categoria actualizada',
                'available' => 0,
            ]);

        $response->assertOk()->assertJsonPath('success', true);

        $categorie->refresh();
        $this->assertSame('Categoria actualizada', $categorie->title);
        $this->assertSame('categoria-actualizada', $categorie->slug);
        $this->assertSame(0, (int) $categorie->available);
    }

    public function test_category_destroy_removes_category(): void
    {
        $categorie = BlogCategorie::factory()->create();

        $this->actingAs($this->manager)
            ->delete(route('manager.blogs.categories.destroy', $categorie->slack))
            ->assertRedirect(route('manager.blogs.categories'));

        $this->assertSoftDeleted('blog_categories', ['id' => $categorie->id]);
    }

    public function test_tag_store_creates_tag(): void
    {
        $response = $this->actingAs($this->manager)
            ->postJson(route('manager.blogs.tags.store'), [
                'title' => 'Etiqueta nueva',
                'available' => 1,
            ]);

        $response->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('blog_tags', [
            'title' => 'Etiqueta nueva',
            'slug' => 'etiqueta-nueva',
        ]);
    }

    public function test_tag_update_edits_tag(): void
    {
        $tag = BlogTag::create([
            'slack' => Str::random(6),
            'title' => 'Original',
            'slug' => 'original',
            'available' => 1,
        ]);

        $response = $this->actingAs($this->manager)
            ->postJson(route('manager.blogs.tags.update'), [
                'slack' => $tag->slack,
                'title' => 'Etiqueta actualizada',
                'available' => 0,
            ]);

        $response->assertOk()->assertJsonPath('success', true);

        $tag->refresh();
        $this->assertSame('Etiqueta actualizada', $tag->title);
        $this->assertSame('etiqueta-actualizada', $tag->slug);
        $this->assertSame(0, (int) $tag->available);
    }

    public function test_tag_destroy_removes_tag(): void
    {
        $tag = BlogTag::create([
            'slack' => Str::random(6),
            'title' => 'Etiqueta a borrar',
            'slug' => 'etiqueta-a-borrar',
            'available' => 1,
        ]);

        $this->actingAs($this->manager)
            ->delete(route('manager.blogs.tags.destroy', $tag->slack))
            ->assertRedirect(route('manager.blogs.tags'));

        $this->assertSoftDeleted('blog_tags', ['id' => $tag->id]);
    }

    public function test_tag_store_rejects_empty_title(): void
    {
        $this->actingAs($this->manager)
            ->postJson(route('manager.blogs.tags.store'), [
                'title' => '',
                'available' => 1,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('title');
    }
}
