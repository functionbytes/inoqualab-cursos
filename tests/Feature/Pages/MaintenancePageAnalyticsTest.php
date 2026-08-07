<?php

namespace Tests\Feature\Pages;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresión: layouts/maintenance.blade.php tenía un snippet de Universal
 * Analytics con un tracking ID ajeno hardcodeado ('UA-216062153-1', residuo
 * del template comercial "Wellearn"), enviando pageviews a una propiedad de
 * GA que no es de este proyecto -- en vez de respetar el mismo ajuste GA4
 * configurable (google_analytics_enable/google_analytics_measurement_id)
 * que ya usa layouts/pages.blade.php.
 */
class MaintenancePageAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_does_not_render_the_hardcoded_foreign_tracking_id(): void
    {
        $html = view('pages.views.maintenance')->render();

        $this->assertStringNotContainsString('UA-216062153-1', $html);
    }

    public function test_renders_configured_ga4_id_when_analytics_enabled(): void
    {
        updateSettings([
            'google_analytics_enable' => 'true',
            'google_analytics_measurement_id' => 'G-TESTID123',
        ]);

        $html = view('pages.views.maintenance')->render();

        $this->assertStringContainsString('G-TESTID123', $html);
        $this->assertStringContainsString('gtag', $html);
    }

    public function test_renders_no_analytics_script_when_disabled(): void
    {
        updateSettings(['google_analytics_enable' => 'false']);

        $html = view('pages.views.maintenance')->render();

        $this->assertStringNotContainsString('gtag', $html);
        $this->assertStringNotContainsString('googletagmanager.com', $html);
    }
}
