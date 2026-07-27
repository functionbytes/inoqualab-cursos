<?php

namespace Tests\Feature\Pages;

use App\Models\Blog\Blog;
use App\Models\Blog\BlogCategorie;
use App\Models\Blog\BlogTag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * `blogs.categories` y `blogs.tags` estaban declaradas en routes/web.php y
 * enlazadas desde la ficha del post y el widget lateral de categorías, pero
 * `BlogController` nunca implementó esos métodos: pulsar una categoría o una
 * etiqueta del blog devolvía un 500 a cualquier visitante.
 */
class BlogTaxonomyPagesTest extends TestCase
{
    use RefreshDatabase;

    private function tag(string $title): BlogTag
    {
        $tag = new BlogTag;
        $tag->slack = Str::random(10);
        $tag->title = $title;
        $tag->slug = Str::slug($title);
        $tag->available = 1;
        $tag->save();

        return $tag;
    }

    public function test_category_page_lists_only_that_categorys_posts(): void
    {
        $inocuidad = BlogCategorie::factory()->create(['title' => 'Inocuidad', 'slug' => 'inocuidad']);
        $otra = BlogCategorie::factory()->create(['title' => 'Otra', 'slug' => 'otra']);

        Blog::factory()->create(['title' => 'POST DE INOCUIDAD', 'categorie_id' => $inocuidad->id]);
        Blog::factory()->create(['title' => 'POST DE OTRA COSA', 'categorie_id' => $otra->id]);

        // Se comprueba el listado, no el HTML completo: la barra lateral de
        // "recientes" muestra los últimos posts de cualquier categoría, así que
        // un assertDontSee sobre la página entera fallaría por diseño.
        $blogs = $this->get(route('blogs.categories', 'inocuidad'))
            ->assertOk()
            ->assertSee('POST DE INOCUIDAD', false)
            ->viewData('blogs');

        $this->assertCount(1, $blogs);
        $this->assertSame('POST DE INOCUIDAD', $blogs->first()->title);
    }

    public function test_unknown_category_returns_404(): void
    {
        $this->get(route('blogs.categories', 'no-existe'))->assertNotFound();
    }

    public function test_tag_page_lists_only_that_tags_posts(): void
    {
        $tag = $this->tag('Laboratorio');
        $etiquetado = Blog::factory()->create(['title' => 'POST ETIQUETADO']);
        Blog::factory()->create(['title' => 'POST SIN ETIQUETA']);

        DB::table('blog_tag')->insert(['tag_id' => $tag->id, 'blog_id' => $etiquetado->id]);

        $blogs = $this->get(route('blogs.tags', 'laboratorio'))
            ->assertOk()
            ->assertSee('POST ETIQUETADO', false)
            ->viewData('blogs');

        $this->assertCount(1, $blogs);
        $this->assertSame('POST ETIQUETADO', $blogs->first()->title);
    }

    public function test_unknown_tag_returns_404(): void
    {
        $this->get(route('blogs.tags', 'no-existe'))->assertNotFound();
    }

    public function test_search_widget_posts_to_a_working_route(): void
    {
        // El buscador lateral publica con Form::open(['route' => ['blogs.filters']]).
        // El método tampoco existía, así que buscar en el blog daba 500.
        Blog::factory()->create(['title' => 'INOCUIDAD EN PLANTA']);
        Blog::factory()->create(['title' => 'OTRO TEMA']);

        $this->post(route('blogs.filters'), ['search' => 'INOCUIDAD'])
            ->assertRedirect(route('blogs', ['search' => 'INOCUIDAD']));

        $blogs = $this->get(route('blogs', ['search' => 'INOCUIDAD']))
            ->assertOk()
            ->viewData('blogs');

        $this->assertCount(1, $blogs);
    }

    public function test_empty_search_returns_to_the_full_listing(): void
    {
        $this->post(route('blogs.filters'), ['search' => '  '])
            ->assertRedirect(route('blogs'));
    }

    public function test_sidebar_counts_only_published_posts(): void
    {
        // El widget contaba con `count($categorie->blogs)`: una consulta por
        // categoría y sin filtrar, así que el número incluía borradores y no
        // cuadraba con los posts realmente listados.
        $categorie = BlogCategorie::factory()->create(['slug' => 'con-borradores']);
        Blog::factory()->count(2)->create(['categorie_id' => $categorie->id]);
        Blog::factory()->unavailable()->create(['categorie_id' => $categorie->id]);

        $categories = $this->get(route('blogs'))->assertOk()->viewData('categories');
        $shown = $categories->firstWhere('slug', 'con-borradores');

        $this->assertSame(2, $shown->blogs_count);
    }

    public function test_post_is_not_listed_among_its_own_related_posts(): void
    {
        $categorie = BlogCategorie::factory()->create();
        $blog = Blog::factory()->create(['title' => 'EL QUE SE LEE', 'categorie_id' => $categorie->id]);
        Blog::factory()->create(['title' => 'OTRO DE LA MISMA', 'categorie_id' => $categorie->id]);

        $relateds = $this->get(route('blogs.view', $blog->slug))
            ->assertOk()
            ->viewData('relateds');

        $this->assertNotContains($blog->id, $relateds->pluck('id')->all());
        $this->assertCount(1, $relateds);
    }

    public function test_sidebar_recents_exclude_drafts(): void
    {
        // En la ficha del post los "recientes" se cargaban sin available().
        Blog::factory()->unavailable()->create(['title' => 'BORRADOR']);
        $blog = Blog::factory()->create(['title' => 'PUBLICADO']);

        $recents = $this->get(route('blogs.view', $blog->slug))
            ->assertOk()
            ->viewData('recents');

        $this->assertNotContains('BORRADOR', $recents->pluck('title')->all());
    }

    public function test_listing_renders_pagination_links_when_needed(): void
    {
        // El controller paginaba pero la vista nunca pintaba los enlaces: al
        // pasar de una página no había forma de llegar a la siguiente.
        Blog::factory()->count(20)->create();

        $this->get(route('blogs'))
            ->assertOk()
            ->assertSee('page=2', false);
    }

    public function test_pagination_keeps_the_active_filter(): void
    {
        $categorie = BlogCategorie::factory()->create(['slug' => 'muchos']);
        Blog::factory()->count(20)->create(['categorie_id' => $categorie->id]);

        $blogs = $this->get(route('blogs.categories', 'muchos'))
            ->assertOk()
            ->viewData('blogs');

        // withQueryString(): pasar a la página 2 no debe perder el filtro.
        $this->assertStringContainsString('/blogs/categories/muchos', $blogs->url(2));
    }

    public function test_search_term_is_shown_back_to_the_user(): void
    {
        Blog::factory()->create(['title' => 'INOCUIDAD EN PLANTA']);

        $this->get(route('blogs', ['search' => 'INOCUIDAD']))
            ->assertOk()
            ->assertSee('INOCUIDAD', false)
            ->assertSee('resultado', false);
    }

    public function test_links_rendered_on_a_post_actually_work(): void
    {
        // El bug entró por aquí: la ficha del post enlaza su categoría y sus
        // etiquetas, así que basta con seguir esos enlaces para reventar.
        $categorie = BlogCategorie::factory()->create(['slug' => 'categoria-viva']);
        $blog = Blog::factory()->create(['categorie_id' => $categorie->id]);

        $this->get(route('blogs.view', $blog->slug))->assertOk();
        $this->get(route('blogs.categories', 'categoria-viva'))->assertOk();
    }
}
