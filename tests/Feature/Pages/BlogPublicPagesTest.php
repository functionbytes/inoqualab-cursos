<?php

namespace Tests\Feature\Pages;

use App\Models\Blog\Blog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Las páginas públicas del blog no tenían ningún test, y por ahí se coló un
 * fallo de compilación real: un buscar-y-reemplazar de `loading="lazy"` había
 * partido la expresión Blade de los parciales `blogs.recents` y `blogs.relateds`
 * (`$recent- loading="lazy">image`), dejando ambas plantillas con PHP inválido.
 *
 * Como el fallo era de compilación, /blogs y /blogs/{slug} reventaban siempre,
 * hubiera o no imagen en el post.
 */
class BlogPublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_index_renders(): void
    {
        Blog::factory()->count(3)->create();

        $this->get(route('blogs'))->assertOk();
    }

    public function test_blog_detail_renders(): void
    {
        $blog = Blog::factory()->create();

        $this->get(route('blogs.view', $blog->slug))
            ->assertOk()
            ->assertSee($blog->title, false);
    }

    public function test_blog_index_falls_back_to_default_image(): void
    {
        // La tabla blogs no tiene columna image/thumbnail: la portada sale de
        // Media Library. Sin media, el parcial `recents` debe caer en la imagen
        // por defecto en vez de romperse.
        Blog::factory()->count(2)->create();

        $this->get(route('blogs'))
            ->assertOk()
            ->assertSee('/pages/images/blog/default.jpg', false);
    }

    public function test_unknown_blog_slug_returns_404(): void
    {
        $this->get(route('blogs.view', 'no-existe-este-post'))->assertNotFound();
    }
}
