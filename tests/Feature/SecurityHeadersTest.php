<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fija las cabeceras de seguridad, en particular el alcance de la CSP: es
 * deliberadamente parcial (sin script-src/style-src, imposibles hoy por el
 * inline de las vistas) y estas aserciones documentan esa decisión.
 */
class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_baseline_headers_are_present(): void
    {
        $response = $this->get(route('index'))->assertOk();

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    public function test_csp_closes_the_vectors_it_can(): void
    {
        $csp = $this->get(route('index'))->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertStringContainsString("base-uri 'self'", $csp);
        $this->assertStringContainsString("frame-ancestors 'self'", $csp);
    }

    public function test_csp_still_allows_the_payment_widget_to_post(): void
    {
        // El widget de Wompi publica su propio formulario hacia checkout.wompi.co:
        // un form-action 'self' a secas rompería el pago.
        $csp = $this->get(route('index'))->headers->get('Content-Security-Policy');

        $this->assertStringContainsString('form-action', $csp);
        $this->assertStringContainsString('https://checkout.wompi.co', $csp);
    }

    public function test_csp_does_not_claim_script_protection_it_cannot_deliver(): void
    {
        // Guardarraíl: si alguien añade script-src, que sea una decisión
        // consciente y no un 'unsafe-inline' que aparente cobertura.
        $csp = $this->get(route('index'))->headers->get('Content-Security-Policy');

        $this->assertStringNotContainsString('unsafe-inline', $csp);
    }

    public function test_csp_declares_no_default_src_fallback(): void
    {
        // `default-src` hace de fallback de script-src: declararlo sin
        // 'unsafe-inline' dejaría el panel entero sin JavaScript inline.
        // Este assert impide que se cuele por descuido.
        $csp = $this->get(route('index'))->headers->get('Content-Security-Policy');

        $this->assertStringNotContainsString('default-src', $csp);
        $this->assertStringNotContainsString('script-src', $csp);
    }
}
