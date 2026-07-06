<?php

namespace Tests\Feature\Managers\Seo;

use App\Services\SeoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresión: SeoService::render() volcaba `schema_custom` con
 * JSON_UNESCAPED_SLASHES, permitiendo que un valor con `</script>` rompiera el
 * tag `<script type="application/ld+json">` e inyectara HTML/JS en el <head>
 * público. El fix agrega JSON_HEX_TAG (y JSON_HEX_AMP) para que '<', '>' y '&'
 * queden como secuencias unicode, sin alterar el valor JSON-LD real.
 */
class SeoJsonLdEscapingTest extends TestCase
{
    use RefreshDatabase;

    public function test_schema_custom_with_script_breakout_does_not_break_the_output_html(): void
    {
        $malicious = '</script><script>alert(document.cookie)</script>';

        $html = (new SeoService)
            ->setTitle('Pagina de prueba', appendSuffix: false)
            ->setSchema([
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'name' => $malicious,
            ])
            ->render();

        $this->assertStringNotContainsString('</script><script>alert', $html);
        $this->assertStringNotContainsString($malicious, $html);

        // El script sigue teniendo exactamente un cierre real </script>.
        $this->assertSame(1, substr_count($html, '</script>'));
    }

    public function test_schema_custom_still_renders_valid_json_ld_after_escaping(): void
    {
        $html = (new SeoService)
            ->setTitle('Pagina de prueba', appendSuffix: false)
            ->setSchema([
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'name' => 'Curso de ejemplo </script>',
                'url' => 'https://example.com/curso',
            ])
            ->render();

        preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches);
        $this->assertNotEmpty($matches, 'No se encontró el bloque JSON-LD en el HTML renderizado.');

        $decoded = json_decode($matches[1], true);

        $this->assertSame('https://schema.org', $decoded['@context']);
        $this->assertSame('Article', $decoded['@type']);
        $this->assertSame('Curso de ejemplo </script>', $decoded['name']);
        $this->assertSame('https://example.com/curso', $decoded['url']);
    }
}
