<?php

namespace Tests\Feature\Seo;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifica que todas las páginas de autenticación NO sean indexables por
 * buscadores (robots: noindex,nofollow) y expongan un título propio vía el
 * SeoService (@seoTags). Regresión de la unificación de SEO del flujo auth.
 */
class AuthSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_noindex(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('name="robots" content="noindex,nofollow"', false);
        $response->assertSee('<title>Ingresar', false);
    }

    public function test_register_page_is_noindex(): void
    {
        $response = $this->get(route('register'));

        $response->assertOk();
        $response->assertSee('name="robots" content="noindex,nofollow"', false);
        $response->assertSee('<title>Registro', false);
    }

    public function test_password_reset_request_page_is_noindex(): void
    {
        $response = $this->get(route('password.reset'));

        $response->assertOk();
        $response->assertSee('name="robots" content="noindex,nofollow"', false);
        $response->assertSee('<title>Recuperar contraseña', false);
    }

    public function test_session_expired_page_is_noindex_with_title(): void
    {
        $response = $this->get(route('session.expired'));

        $response->assertOk();
        $response->assertSee('name="robots" content="noindex,nofollow"', false);
        $response->assertSee('<title>Sesión expirada', false);
    }

    public function test_session_expired_device_variant_has_own_title(): void
    {
        $response = $this->get(route('session.expired', ['reason' => 'device']));

        $response->assertOk();
        $response->assertSee('name="robots" content="noindex,nofollow"', false);
        $response->assertSee('<title>Sesión en otro dispositivo', false);
    }
}
