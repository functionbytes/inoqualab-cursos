<?php

namespace Tests\Unit\Services;

use App\Services\AnalyticsReportService;
use Spatie\Analytics\Facades\Analytics;
use Spatie\Analytics\Period;
use Tests\TestCase;

/**
 * Regresión: el mapeo de "top referrers" leía $item['sessionSource'], una
 * clave que Analytics::fetchTopReferrers() nunca produce -- el paquete pide
 * la dimensión 'pageReferrer' a GA4 (ver Analytics::fetchTopReferrers() en
 * spatie/laravel-analytics), así que la clave real de cada fila es
 * 'pageReferrer'. Todas las filas caían al fallback 'Direct' sin importar
 * la fuente real del tráfico -- cualquier reporte mostraba "todo el tráfico
 * es directo" aunque no lo fuera.
 */
class AnalyticsReportServiceTest extends TestCase
{
    public function test_top_referrers_reads_the_real_source_not_always_direct(): void
    {
        // El fake del paquete devuelve la MISMA colección para cualquier
        // método (get/fetchMostVisitedPages/fetchTopBrowsers/
        // fetchTopReferrers); alcanza con una fila con forma de "referrer"
        // para probar específicamente ese mapeo -- las otras secciones del
        // reporte quedan con sus valores por defecto, lo cual es aceptable
        // para esta prueba puntual.
        Analytics::fake(collect([
            ['pageReferrer' => 'google.com', 'screenPageViews' => 120],
        ]));

        $report = (new AnalyticsReportService)->generateReport(Period::days(7), 'weekly');

        $this->assertSame('google.com', $report['top_referrers'][0]['source']);
        $this->assertSame(120, $report['top_referrers'][0]['views']);
    }

    public function test_top_referrers_falls_back_to_direct_when_the_dimension_is_genuinely_missing(): void
    {
        Analytics::fake(collect([
            ['pageReferrer' => '(direct)', 'screenPageViews' => 10],
        ]));

        $report = (new AnalyticsReportService)->generateReport(Period::days(7), 'weekly');

        $this->assertSame('(direct)', $report['top_referrers'][0]['source']);
    }
}
