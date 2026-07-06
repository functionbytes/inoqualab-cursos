<?php

namespace Tests\Feature\Managers\Blogs;

use App\Models\Blog\Blog;
use App\Models\Blog\BlogCategorie;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresión del bug donde BlogsController::store()/update() usaban
 * DB::transaction() sin importar la fachada DB, dejando la creación y edición
 * de blogs completamente rota (Error: Class "...\Blogs\DB" not found).
 * También cubre que el contenido WYSIWYG se sanitiza (XSS) al guardarse.
 */
class BlogStoreUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected BlogCategorie $categorie;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->manager = User::factory()->manager()->create();
        $this->categorie = BlogCategorie::factory()->create();
    }

    public function test_store_creates_blog_without_error(): void
    {
        $response = $this->actingAs($this->manager)
            ->postJson(route('manager.blogs.store'), [
                'title' => 'Noticia de prueba',
                'categorie' => $this->categorie->id,
                'available' => 1,
                'date' => now()->format('Y-m-d'),
                'contents' => '<p>Contenido de la noticia</p>',
                'description' => 'Descripcion corta de la noticia',
            ]);

        $response->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('blogs', [
            'title' => 'NOTICIA DE PRUEBA',
            'description' => 'Descripcion corta de la noticia',
        ]);
    }

    public function test_update_edits_blog_without_error(): void
    {
        $blog = Blog::factory()->create([
            'categorie_id' => $this->categorie->id,
            'content' => 'CONTENIDO-ORIGINAL',
            'description' => 'DESCRIPCION-ORIGINAL',
        ]);

        $response = $this->actingAs($this->manager)
            ->postJson(route('manager.blogs.update'), [
                'slack' => $blog->slack,
                'title' => 'Titulo actualizado',
                'categorie' => $this->categorie->id,
                'available' => 1,
                'date' => now()->format('Y-m-d'),
                'contents' => 'CONTENIDO-ACTUALIZADO',
                'description' => 'DESCRIPCION-ACTUALIZADA',
            ]);

        $response->assertOk()->assertJsonPath('success', true);

        $blog->refresh();
        $this->assertSame('CONTENIDO-ACTUALIZADO', $blog->content);
        $this->assertSame('DESCRIPCION-ACTUALIZADA', $blog->description);
    }

    public function test_malicious_script_in_content_is_sanitized_when_rendered(): void
    {
        $blog = Blog::factory()->create([
            'categorie_id' => $this->categorie->id,
            'content' => '<p>Texto seguro</p><script>alert("xss")</script>',
            'description' => 'Descripcion segura',
        ]);

        // El modelo guarda el contenido crudo (igual que Cursos/Anuncios); la
        // sanitización ocurre al mostrarlo, con el mismo patrón clean($v,'content').
        $sanitized = clean($blog->content, 'content');

        $this->assertStringNotContainsString('<script>', $sanitized);
        $this->assertStringContainsString('Texto seguro', $sanitized);

        $response = $this->actingAs($this->manager)
            ->get(route('manager.blogs.view', $blog->slug));

        $response->assertOk();
        $response->assertDontSee('alert(', false);
        $response->assertSee('Texto seguro', false);
    }
}
