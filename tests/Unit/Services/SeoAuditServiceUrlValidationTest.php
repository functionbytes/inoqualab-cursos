<?php

namespace Tests\Unit\Services;

use App\Services\SeoAuditService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Regresión: validatePublicUrl() resolvía el host y validaba ESA IP, pero
 * la petición real (Http::get()) hacía su propia resolución DNS por
 * separado -- un dominio con TTL bajo (DNS rebinding) podía apuntar a una
 * IP pública en el primer lookup (pasa el guard) y a una IP privada en el
 * segundo (SSRF pese a la validación). Ahora la IP validada se fija con
 * CURLOPT_RESOLVE, así la petición real usa exactamente esa IP.
 */
class SeoAuditServiceUrlValidationTest extends TestCase
{
    private function callValidatePublicUrl(string $url): string
    {
        $service = new SeoAuditService;
        $method = new \ReflectionMethod($service, 'validatePublicUrl');
        $method->setAccessible(true);

        return $method->invoke($service, $url);
    }

    public function test_rejects_urls_resolving_to_a_private_ip(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('redes privadas');

        $this->callValidatePublicUrl('http://localhost/algo');
    }

    public function test_rejects_non_http_schemes(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('http/https');

        $this->callValidatePublicUrl('ftp://example.com/algo');
    }

    public function test_audit_url_pins_the_request_to_the_pre_validated_ip(): void
    {
        Http::fake(['*' => Http::response('<html><body>hola</body></html>', 200)]);

        $result = (new SeoAuditService)->auditUrl('http://example.com/pagina');

        // errorResult() (la rama catch) devuelve 'url' => '' -- si la
        // petición real hubiera fallado (p. ej. porque CURLOPT_RESOLVE está
        // mal construido: host/puerto vacíos, tipo incorrecto, etc.),
        // caeríamos ahí en vez de completar la auditoría. Http::fake()
        // intercepta antes de que cURL evalúe la opción, así que esto no
        // prueba el pinning DNS en sí (Http::assertSent() no expone las
        // opciones curl crudas), pero sí confirma que construir y pasar
        // CURLOPT_RESOLVE no rompe el flujo normal.
        $this->assertSame('http://example.com/pagina', $result['url']);
        Http::assertSent(fn ($request) => $request->url() === 'http://example.com/pagina');
    }
}
