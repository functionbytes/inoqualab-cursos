<?php

namespace Tests\Feature\Pages;

use App\Models\Setting\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresión: /politics, /terms, y el footer público (incluido en TODAS las
 * páginas del frontend) imprimían page_politic/page_term/page_description
 * con {!! !!} sin pasar por el purificador -- stored XSS alcanzable por
 * cualquier visitante anónimo del sitio, no solo el panel admin, si esos
 * campos llegaran a contener HTML malicioso (guardados sin sanear desde
 * SettingsController::update(), que solo hace str_replace() de placeholders
 * de Quill).
 */
class PublicSettingsXssTest extends TestCase
{
    use RefreshDatabase;

    public function test_politics_page_purifies_stored_content(): void
    {
        Setting::create(['key' => 'page_politic', 'value' => '<p>Política</p><script>alert(1)</script>']);

        $response = $this->get(route('politics'));

        $response->assertOk();
        $response->assertDontSee('<script>alert(1)</script>', false);
        $response->assertSee('<p>Política</p>', false);
    }

    public function test_terms_page_purifies_stored_content(): void
    {
        Setting::create(['key' => 'page_term', 'value' => '<p>Términos</p><script>alert(1)</script>']);

        $response = $this->get(route('terms'));

        $response->assertOk();
        $response->assertDontSee('<script>alert(1)</script>', false);
        $response->assertSee('<p>Términos</p>', false);
    }

    public function test_public_footer_purifies_page_description_on_every_page(): void
    {
        Setting::create(['key' => 'page_description', 'value' => '<p>Sobre nosotros</p><script>alert(1)</script>']);

        $response = $this->get(route('index'));

        $response->assertOk();
        $response->assertDontSee('<script>alert(1)</script>', false);
    }
}
