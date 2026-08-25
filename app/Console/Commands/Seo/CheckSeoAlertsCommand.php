<?php

namespace App\Console\Commands\Seo;

use App\Models\Seo\SeoAlert;
use App\Models\Seo\SeoWebVital;
use App\Services\RedirectChainDetector;
use Illuminate\Console\Command;

/**
 * Corre las detecciones SEO que no tienen otro disparador natural (a
 * diferencia de score_drop y new_404, que se levantan en el momento en que
 * ocurren -- auditMeta() / Seo404Log::recordHit()) y las registra como
 * seo_alerts:
 *
 * - Cadenas de redirect: RedirectChainDetector::detectAll() ya levanta la
 *   alerta internamente; antes solo se invocaba manualmente desde el botón
 *   "Analizar cadenas" del panel, así que sin este comando nadie las
 *   detectaba hasta que un manager entrara a mirar.
 * - Core Web Vitals pobres: agrega seo_web_vitals (alimentado por el beacon
 *   del frontend) por página+métrica de los últimos 7 días.
 */
class CheckSeoAlertsCommand extends Command
{
    protected $signature = 'seo:check-alerts';

    protected $description = 'Detecta cadenas de redirect y Core Web Vitals pobres, y las registra en seo_alerts';

    /** Mínimo de muestras en la ventana para no alertar por una sola visita mala. */
    private const MIN_SAMPLES = 3;

    public function handle(RedirectChainDetector $detector): int
    {
        $chains = $detector->detectAll();
        $this->info("Cadenas de redirect detectadas: {$chains->count()}");

        $vitalsRaised = $this->checkPoorWebVitals();
        $this->info("Alertas de Web Vitals generadas: {$vitalsRaised}");

        return self::SUCCESS;
    }

    private function checkPoorWebVitals(): int
    {
        $since = now()->subDays(7);
        $raised = 0;

        $worst = SeoWebVital::query()
            ->selectRaw('url_path, metric, COUNT(*) as samples, AVG(value) as avg_value')
            ->where('captured_at', '>=', $since)
            ->where('rating', 'poor')
            ->groupBy('url_path', 'metric')
            ->havingRaw('COUNT(*) >= ?', [self::MIN_SAMPLES])
            ->get();

        foreach ($worst as $row) {
            SeoAlert::raise(
                SeoAlert::TYPE_VITAL_POOR,
                SeoAlert::SEVERITY_WARNING,
                "Core Web Vital pobre: {$row->metric} en {$row->url_path}",
                "Promedio de {$row->metric}: ".round((float) $row->avg_value, 1)." en {$row->samples} muestras (últimos 7 días).",
                $row->url_path,
                [
                    'metric' => $row->metric,
                    'avg_value' => round((float) $row->avg_value, 3),
                    'samples' => (int) $row->samples,
                ]
            );
            $raised++;
        }

        return $raised;
    }
}
