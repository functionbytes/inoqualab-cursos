<?php

namespace Tests\Feature\Seo;

use App\Console\Commands\Seo\CheckSeoAlertsCommand;
use App\Models\Seo\Seo404Log;
use App\Models\Seo\SeoAlert;
use App\Models\Seo\SeoMeta;
use App\Models\Seo\SeoRedirect;
use App\Models\Seo\SeoWebVital;
use App\Services\RedirectChainDetector;
use App\Services\SeoAuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * seo_alerts existía y el panel (SeoAlertsController) ya sabía leerla, pero
 * NADA en el código escribía en ella -- 0 filas siempre. Cubre los 4
 * productores agregados: new_404 (Seo404Log::recordHit), score_drop
 * (SeoAuditService::auditMeta), redirect_chain (RedirectChainDetector) y
 * vital_poor (seo:check-alerts).
 */
class SeoAlertsProducersTest extends TestCase
{
    use RefreshDatabase;

    // ── new_404 ──────────────────────────────────────────────────────────────

    public function test_first_404_hit_raises_a_new_404_alert(): void
    {
        $this->get('/esta-ruta-no-existe-'.uniqid())->assertNotFound();

        $this->assertSame(1, SeoAlert::where('type', SeoAlert::TYPE_NEW_404)->count());
    }

    /**
     * Regresión encontrada al escribir el test anterior: abort(404) dentro de
     * un controller YA enrutado lanza la misma NotFoundHttpException que una
     * ruta inexistente. Antes del fix, TrackSeo404 solo miraba el status code
     * DESPUÉS de $next($request) -- una excepción deshace la pila de
     * middleware sin llegar nunca ahí, así que abort(404) tampoco se rastreaba.
     */
    public function test_abort_404_inside_a_routed_controller_also_raises_an_alert(): void
    {
        Route::get('/test-abort-404-ruta', function () {
            abort(404);
        })->middleware('web');

        $this->get('/test-abort-404-ruta')->assertNotFound();

        $this->assertSame(1, Seo404Log::count());
        $this->assertSame(1, SeoAlert::where('type', SeoAlert::TYPE_NEW_404)->count());
    }

    public function test_repeated_404_hits_do_not_raise_a_second_alert(): void
    {
        $path = '/esta-ruta-no-existe-'.uniqid();

        $this->get($path)->assertNotFound();
        $this->get($path)->assertNotFound();
        $this->get($path)->assertNotFound();

        $this->assertSame(1, SeoAlert::where('type', SeoAlert::TYPE_NEW_404)->count());
    }

    // ── score_drop ───────────────────────────────────────────────────────────

    public function test_auditing_a_meta_for_the_first_time_never_raises_score_drop(): void
    {
        $meta = SeoMeta::create(['seoable_type' => 'App\\Models\\Course\\Course', 'seoable_id' => 1, 'locale' => 'es']);

        (new SeoAuditService)->auditMeta($meta);

        $this->assertSame(0, SeoAlert::where('type', SeoAlert::TYPE_SCORE_DROP)->count());
    }

    public function test_a_significant_score_drop_raises_an_alert(): void
    {
        $meta = SeoMeta::create([
            'seoable_type' => 'App\\Models\\Course\\Course',
            'seoable_id' => 1,
            'locale' => 'es',
            'title' => str_repeat('a', 60),
            'description' => str_repeat('b', 160),
            'og_image' => 'x.jpg',
        ]);

        // Primera auditoría: con title/description/og_image completos, score alto.
        (new SeoAuditService)->auditMeta($meta);

        // Se vacían los campos que el checker premia -> score cae bastante.
        $meta->update(['title' => null, 'description' => null, 'og_image' => null]);
        (new SeoAuditService)->auditMeta($meta->fresh());

        $alert = SeoAlert::where('type', SeoAlert::TYPE_SCORE_DROP)->first();
        $this->assertNotNull($alert, 'No se levantó la alerta de caída de score.');
        $this->assertSame($meta->id, $alert->context['seo_meta_id']);
    }

    public function test_a_small_score_fluctuation_does_not_raise_an_alert(): void
    {
        $meta = SeoMeta::create([
            'seoable_type' => 'App\\Models\\Course\\Course',
            'seoable_id' => 1,
            'locale' => 'es',
            'title' => str_repeat('a', 60),
        ]);

        (new SeoAuditService)->auditMeta($meta);
        // Mismo contenido -> mismo score, sin caída.
        (new SeoAuditService)->auditMeta($meta->fresh());

        $this->assertSame(0, SeoAlert::where('type', SeoAlert::TYPE_SCORE_DROP)->count());
    }

    // ── redirect_chain ───────────────────────────────────────────────────────

    public function test_detect_all_raises_a_redirect_chain_alert(): void
    {
        SeoRedirect::create(['source_path' => '/a', 'target_path' => '/b', 'status_code' => 301, 'is_active' => true]);
        SeoRedirect::create(['source_path' => '/b', 'target_path' => '/c', 'status_code' => 301, 'is_active' => true]);

        (new RedirectChainDetector)->detectAll();

        $alert = SeoAlert::where('type', SeoAlert::TYPE_REDIRECT_CHAIN)->first();
        $this->assertNotNull($alert);
        $this->assertSame('/a', $alert->url);
    }

    public function test_detect_all_does_not_raise_when_there_is_no_chain(): void
    {
        SeoRedirect::create(['source_path' => '/a', 'target_path' => '/b', 'status_code' => 301, 'is_active' => true]);

        (new RedirectChainDetector)->detectAll();

        $this->assertSame(0, SeoAlert::where('type', SeoAlert::TYPE_REDIRECT_CHAIN)->count());
    }

    // ── vital_poor ───────────────────────────────────────────────────────────

    public function test_check_alerts_command_raises_vital_poor_with_enough_samples(): void
    {
        for ($i = 0; $i < 3; $i++) {
            SeoWebVital::create([
                'url' => 'https://training.test/curso/lento',
                'url_path' => '/curso/lento',
                'metric' => 'LCP',
                'value' => 5000,
                'rating' => 'poor',
                'captured_at' => now(),
            ]);
        }

        $this->artisan(CheckSeoAlertsCommand::class)->assertSuccessful();

        $alert = SeoAlert::where('type', SeoAlert::TYPE_VITAL_POOR)->first();
        $this->assertNotNull($alert);
        $this->assertSame('/curso/lento', $alert->url);
        $this->assertSame(3, $alert->context['samples']);
    }

    public function test_check_alerts_command_ignores_pages_below_the_sample_threshold(): void
    {
        SeoWebVital::create([
            'url' => 'https://training.test/curso/casi-sin-datos',
            'url_path' => '/curso/casi-sin-datos',
            'metric' => 'LCP',
            'value' => 5000,
            'rating' => 'poor',
            'captured_at' => now(),
        ]);

        $this->artisan(CheckSeoAlertsCommand::class)->assertSuccessful();

        $this->assertSame(0, SeoAlert::where('type', SeoAlert::TYPE_VITAL_POOR)->count());
    }

    public function test_check_alerts_command_ignores_good_ratings(): void
    {
        for ($i = 0; $i < 3; $i++) {
            SeoWebVital::create([
                'url' => 'https://training.test/curso/rapido',
                'url_path' => '/curso/rapido',
                'metric' => 'LCP',
                'value' => 1200,
                'rating' => 'good',
                'captured_at' => now(),
            ]);
        }

        $this->artisan(CheckSeoAlertsCommand::class)->assertSuccessful();

        $this->assertSame(0, SeoAlert::where('type', SeoAlert::TYPE_VITAL_POOR)->count());
    }
}
