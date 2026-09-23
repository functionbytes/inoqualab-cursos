<?php

namespace Tests\Feature\Seo;

use App\Models\Seo\SeoWebVital;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El endpoint (seo.web-vitals.beacon -> SeoWebVitalsController::store) y el
 * panel que lo lee (manager/seo/web-vitals) ya existían, pero ningún frontend
 * lo llamaba -- el panel siempre mostraba "sin datos". Estos tests cubren
 * tanto el endpoint (que nunca tuvo cobertura) como que el beacon JS quedó
 * inyectado en el layout público.
 */
class WebVitalsBeaconTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders_the_web_vitals_beacon_script(): void
    {
        $response = $this->get(route('index'));

        $response->assertOk();
        $response->assertSee(route('seo.web-vitals.beacon'), false);
        $response->assertSee(asset('pages/js/layout-cart.js'), false);

        // El JS que llama navigator.sendBeacon vive en un archivo externo
        // (regla del proyecto: nada de <script> inline en resources/views/pages/),
        // no en el HTML servido por route('index').
        $this->assertStringContainsString(
            'navigator.sendBeacon',
            file_get_contents(public_path('pages/js/layout-cart.js'))
        );
    }

    public function test_beacon_endpoint_stores_a_valid_metric(): void
    {
        $this->postJson(route('seo.web-vitals.beacon'), [
            'metric' => 'LCP',
            'value' => 2100.5,
            'url' => 'https://training.test/curso/algo',
            'navigation_type' => 'navigate',
        ])->assertCreated();

        $this->assertDatabaseHas('seo_web_vitals', [
            'metric' => 'LCP',
            'url_path' => '/curso/algo',
            'rating' => 'good',
        ]);
    }

    public function test_beacon_endpoint_infers_device_from_user_agent_when_not_sent(): void
    {
        $this->postJson(
            route('seo.web-vitals.beacon'),
            ['metric' => 'CLS', 'value' => 0.05, 'url' => 'https://training.test/'],
            ['User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) Mobile/15E148']
        )->assertCreated();

        $this->assertDatabaseHas('seo_web_vitals', [
            'metric' => 'CLS',
            'device' => 'mobile',
        ]);
    }

    public function test_beacon_endpoint_rejects_an_unknown_metric(): void
    {
        $this->postJson(route('seo.web-vitals.beacon'), [
            'metric' => 'NOT-A-METRIC',
            'value' => 1,
            'url' => 'https://training.test/',
        ])->assertUnprocessable();

        $this->assertSame(0, SeoWebVital::count());
    }

    public function test_beacon_endpoint_is_rate_limited(): void
    {
        $payload = ['metric' => 'FCP', 'value' => 900, 'url' => 'https://training.test/'];

        for ($i = 0; $i < 120; $i++) {
            $this->postJson(route('seo.web-vitals.beacon'), $payload)->assertCreated();
        }

        $this->postJson(route('seo.web-vitals.beacon'), $payload)->assertStatus(429);
    }
}
