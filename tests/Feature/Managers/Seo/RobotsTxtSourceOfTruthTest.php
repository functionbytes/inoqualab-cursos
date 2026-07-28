<?php

namespace Tests\Feature\Managers\Seo;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * El robots.txt se guardaba en dos sitios a la vez: el ajuste `robots_txt` y el
 * archivo public/robots.txt. Como el servidor web sirve los estáticos antes de
 * pasar a PHP, ese archivo dejaba muerta la ruta dinámica — y con ella la línea
 * `Sitemap:`, que RobotsTxtController genera con el dominio real del entorno.
 * El resultado: editar desde el panel no cambiaba nada de lo que veían los
 * buscadores, y el sitemap quedaba congelado al dominio de cuando se guardó.
 *
 * Ahora la única fuente de verdad es el ajuste.
 */
class RobotsTxtSourceOfTruthTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->manager = User::factory()->manager()->create();

        // Estado de partida limpio: sin archivo estático.
        if (File::exists(public_path('robots.txt'))) {
            File::delete(public_path('robots.txt'));
        }
    }

    protected function tearDown(): void
    {
        if (File::exists(public_path('robots.txt'))) {
            File::delete(public_path('robots.txt'));
        }

        parent::tearDown();
    }

    public function test_saving_does_not_create_the_static_file(): void
    {
        $this->actingAs($this->manager)
            ->postJson(route('manager.seo.robots.update'), [
                'robots_txt' => "User-agent: *\nDisallow: /privado/",
            ])
            ->assertOk();

        $this->assertFileDoesNotExist(
            public_path('robots.txt'),
            'Volvió a escribirse el estático: eclipsaría la ruta dinámica.'
        );
    }

    public function test_saving_removes_a_leftover_static_file(): void
    {
        // Escenario real: el archivo quedó de la versión anterior y hay que
        // deshacerse de él para devolver el control a la ruta.
        File::put(public_path('robots.txt'), "User-agent: *\nDisallow: /todo-viejo/");

        $this->actingAs($this->manager)
            ->postJson(route('manager.seo.robots.update'), [
                'robots_txt' => "User-agent: *\nAllow: /",
            ])
            ->assertOk();

        $this->assertFileDoesNotExist(public_path('robots.txt'));
    }

    public function test_the_route_serves_what_was_saved(): void
    {
        $this->actingAs($this->manager)
            ->postJson(route('manager.seo.robots.update'), [
                'robots_txt' => "User-agent: *\nDisallow: /secreto/",
            ])
            ->assertOk();

        $this->get('/robots.txt')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=utf-8')
            ->assertSee('Disallow: /secreto/', false);
    }

    public function test_the_sitemap_line_uses_the_current_domain(): void
    {
        // Es lo que se perdía con el archivo estático: la URL del sitemap
        // quedaba congelada al dominio del entorno donde se guardó.
        $this->actingAs($this->manager)
            ->postJson(route('manager.seo.robots.update'), [
                'robots_txt' => "User-agent: *\nAllow: /\n\nSitemap: https://dominio-viejo.example/sitemap.xml",
            ])
            ->assertOk();

        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee(route('sitemap.index'), false)
            ->assertDontSee('dominio-viejo.example', false);
    }
}
