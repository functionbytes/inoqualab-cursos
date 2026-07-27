<?php

namespace Tests\Feature\Pages;

use App\Models\Blog\Blog;
use App\Models\Blog\BlogCategorie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * El blog público arrastraba dos N+1:
 *
 * - el widget de categorías contaba con `count($categorie->blogs)` dentro del
 *
 *   @foreach, lo que cargaba la relación entera una vez por categoría;
 * - la tarjeta de cada post llama `getMedia('thumbnail')`, que sin eager load
 *   consulta la tabla `media` una vez por post.
 *
 * Se fija un techo de consultas en vez de comparar dos escenarios: comparar es
 * frágil porque el caché de ajustes se calienta en la primera petición y
 * falsea la segunda medición.
 */
class BlogQueryEfficiencyTest extends TestCase
{
    use RefreshDatabase;

    /** Techo holgado: con N+1 real este número se dispara con los datos de abajo. */
    private const MAX_QUERIES = 15;

    private function measure(string $url): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->get($url)->assertOk();
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    }

    private function seedBlog(): void
    {
        // 12 categorías × 3 posts: con el N+1 del widget serían 12 consultas
        // extra, más una por cada post pintado en la página.
        BlogCategorie::factory()->count(12)->create()
            ->each(fn ($c) => Blog::factory()->count(3)->create(['categorie_id' => $c->id]));
    }

    public function test_listing_stays_under_the_query_budget(): void
    {
        $this->seedBlog();

        $queries = $this->measure(route('blogs'));

        $this->assertLessThanOrEqual(
            self::MAX_QUERIES,
            $queries,
            "El listado del blog hizo {$queries} consultas (techo: ".self::MAX_QUERIES.'). Revisa si volvió un N+1.'
        );
    }

    public function test_post_detail_stays_under_the_query_budget(): void
    {
        $this->seedBlog();
        $blog = Blog::first();

        $queries = $this->measure(route('blogs.view', $blog->slug));

        $this->assertLessThanOrEqual(
            self::MAX_QUERIES,
            $queries,
            "La ficha del post hizo {$queries} consultas (techo: ".self::MAX_QUERIES.').'
        );
    }

    public function test_category_listing_stays_under_the_query_budget(): void
    {
        $this->seedBlog();
        $categorie = BlogCategorie::first();

        $queries = $this->measure(route('blogs.categories', $categorie->slug));

        $this->assertLessThanOrEqual(
            self::MAX_QUERIES,
            $queries,
            "El listado por categoría hizo {$queries} consultas (techo: ".self::MAX_QUERIES.').'
        );
    }
}
