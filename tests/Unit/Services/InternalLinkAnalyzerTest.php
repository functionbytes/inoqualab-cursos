<?php

namespace Tests\Unit\Services;

use App\Services\InternalLinkAnalyzer;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Regresión: findOrphansByLinks() solo escaneaba enlaces salientes de las
 * primeras 30 URLs (Http::get() en un foreach con take(30)), pero calculaba
 * "huérfanas" contra la colección COMPLETA de URLs. Cualquier URL fuera de
 * esas 30 se reportaba como huérfana sin importar si tenía enlaces
 * entrantes reales -- simplemente porque nunca se buscó en las páginas que
 * la enlazan (que podían estar más allá de la posición 30).
 */
class InternalLinkAnalyzerTest extends TestCase
{
    public function test_urls_beyond_the_scan_limit_are_not_reported_as_orphans(): void
    {
        // 31 URLs: solo las primeras 30 se escanean como fuente. Las 30
        // primeras se enlazan entre sí en cadena (1→2→3→...→30), así que
        // NINGUNA de ellas es huérfana -- el único candidato que puede
        // colarse en el resultado es la #31, que queda fuera del escaneo.
        $urls = collect(range(1, 31))->map(fn ($i) => "https://example.test/pagina-{$i}");

        // Cada página 1..29 enlaza a la siguiente (2..30) -- ninguna de ellas
        // queda huérfana. La #30 (última escaneada) NO enlaza a la #31: así
        // la #31, si en la realidad tuviera enlaces entrantes desde alguna
        // página 32+ nunca escaneada, esos enlaces simplemente no se
        // detectan -- lo correcto es no afirmar nada sobre ella, no darla
        // por huérfana.
        Http::fake(function ($request) {
            $url = (string) $request->url();
            preg_match('/pagina-(\d+)/', $url, $m);
            $n = (int) $m[1];

            if ($n >= 30) {
                return Http::response('sin enlaces', 200);
            }

            $next = $n + 1;

            return Http::response("<a href=\"https://example.test/pagina-{$next}\">siguiente</a>", 200);
        });

        $result = (new InternalLinkAnalyzer)->findOrphansByLinks($urls, limit: 50);

        $this->assertSame(30, $result['scanned']);
        $this->assertSame(31, $result['total_urls']);

        // La #31 nunca se escaneó como fuente NI se sabe si tiene enlaces
        // entrantes reales desde páginas fuera de las 30 -- antes aparecía
        // igual como huérfana solo por estar fuera del rango escaneado.
        $this->assertNotContains('https://example.test/pagina-31', $result['orphans']);
    }

    public function test_a_page_with_no_inbound_links_among_the_scanned_set_is_reported_as_orphan(): void
    {
        $urls = collect(['https://example.test/a', 'https://example.test/b']);

        Http::fake([
            'https://example.test/a' => Http::response('<a href="/b">link</a>', 200),
            'https://example.test/b' => Http::response('sin enlaces', 200),
        ]);

        $result = (new InternalLinkAnalyzer)->findOrphansByLinks($urls);

        // /a nunca es enlazada por nadie -> huérfana. /b sí lo es por /a.
        $this->assertContains('https://example.test/a', $result['orphans']);
        $this->assertNotContains('https://example.test/b', $result['orphans']);
    }
}
