<?php

namespace Tests\Feature\Seo;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Blinda el SEO estructural del frontend público:
 * - robots.txt dinámico bloquea TODOS los portales privados y referencia el
 *   sitemap con el dominio del entorno (no hardcodeado).
 * - sitemap incluye las páginas públicas y excluye /commercial (contenido demo).
 * - la home emite el JSON-LD Organization.
 */
class StructuralSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_robots_txt_blocks_all_private_portals(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        foreach (['/panel/', '/customer/', '/distributor/', '/enterprise/', '/support/', '/accounting/', '/api/'] as $path) {
            $response->assertSee('Disallow: '.$path, false);
        }
    }

    public function test_robots_txt_references_sitemap_with_environment_domain(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        // La línea Sitemap la añade el controller con route('sitemap.index') (dominio real, no hardcodeado).
        $response->assertSee('Sitemap: '.route('sitemap.index'), false);
    }

    public function test_sitemap_includes_public_pages(): void
    {
        $response = $this->get('/sitemap-main.xml');

        $response->assertOk();
        foreach (['/courses', '/instructions', '/contacts', '/faqs', '/certifiers'] as $path) {
            $response->assertSee(url($path), false);
        }
    }

    public function test_sitemap_excludes_commercial_demo_page(): void
    {
        $response = $this->get('/sitemap-main.xml');

        $response->assertOk();
        $response->assertDontSee(url('/commercial'), false);
    }

    public function test_home_emits_organization_jsonld(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('"@type":"Organization"', false);
    }
}
