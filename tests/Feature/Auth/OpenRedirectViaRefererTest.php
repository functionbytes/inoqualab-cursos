<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresión: Authenticate::handle() (y su duplicado en bootstrap/app.php,
 * en el handler de TokenMismatchException) guardaban el header Referer tal
 * cual en session('url.intended') sin validar que fuera del mismo host.
 * redirect()->intended() (llamado tras un login exitoso en LoginController)
 * usa ese valor literal como destino -- una URL externa ahí es un open
 * redirect post-login: un POST cross-site con Referer a un dominio ajeno
 * bastaba para envenenar el destino del próximo login legítimo.
 */
class OpenRedirectViaRefererTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_post_with_external_referer_does_not_poison_intended_url(): void
    {
        $this->post(route('manager.users.store'), [], [
            'referer' => 'https://evil-phishing.example.com/login-fake',
        ])->assertRedirect(route('session.expired'));

        $this->assertNotSame('https://evil-phishing.example.com/login-fake', session('url.intended'));
    }

    public function test_guest_post_with_same_host_referer_still_sets_intended_url(): void
    {
        $sameHostReferer = url('/algun-formulario');

        $this->post(route('manager.users.store'), [], [
            'referer' => $sameHostReferer,
        ])->assertRedirect(route('session.expired'));

        $this->assertSame($sameHostReferer, session('url.intended'));
    }

    public function test_guest_post_with_lookalike_subdomain_referer_is_rejected(): void
    {
        // parse_url(...)->host debe compararse exacto -- un prefijo de string
        // (str_starts_with($referer, url('/'))) habría dejado pasar este
        // dominio ajeno, ya que "https://training.test.evil.com" empieza
        // literalmente con "https://training.test".
        $lookalike = rtrim(url('/'), '/').'.evil.com/fake';

        $this->post(route('manager.users.store'), [], [
            'referer' => $lookalike,
        ])->assertRedirect(route('session.expired'));

        $this->assertNotSame($lookalike, session('url.intended'));
    }
}
