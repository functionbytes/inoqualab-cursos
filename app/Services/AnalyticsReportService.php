<?php

namespace App\Services;

use App\Exports\Analytics\AnalyticsReportExport;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Analytics\Facades\Analytics;
use Spatie\Analytics\Period;

class AnalyticsReportService
{
    public function generateReport(Period $period, string $reportType): array
    {
        $overview = Analytics::get(
            period: $period,
            metrics: ['sessions', 'totalUsers', 'screenPageViews', 'bounceRate'],
            maxResults: 1,
        );

        $totals = $overview->first() ?? [];

        $topPages = Analytics::fetchMostVisitedPages($period, 10)
            ->map(fn ($page) => [
                'title' => $page['pageTitle'] ?? 'Unknown',
                'url' => $page['fullPageUrl'] ?? '#',
                'views' => (int) ($page['screenPageViews'] ?? 0),
            ])
            ->toArray();

        $topBrowsers = Analytics::fetchTopBrowsers($period)
            ->map(fn ($item) => [
                'name' => $item['browser'] ?? 'Unknown',
                'sessions' => (int) ($item['sessions'] ?? 0),
            ])
            ->toArray();

        $topReferrers = Analytics::fetchTopReferrers($period, 10)
            ->map(fn ($item) => [
                'source' => $item['sessionSource'] ?? 'Direct',
                'views' => (int) ($item['screenPageViews'] ?? 0),
            ])
            ->toArray();

        return [
            'type' => $reportType,
            'generated_at' => now()->toIso8601String(),
            'period' => [
                'start' => $period->startDate->toDateString(),
                'end' => $period->endDate->toDateString(),
            ],
            'overview' => [
                'sessions' => (int) ($totals['sessions'] ?? 0),
                'users' => (int) ($totals['totalUsers'] ?? 0),
                'pageviews' => (int) ($totals['screenPageViews'] ?? 0),
                'bounce_rate' => (float) ($totals['bounceRate'] ?? 0),
            ],
            'top_pages' => $topPages,
            'top_browsers' => $topBrowsers,
            'top_referrers' => $topReferrers,
        ];
    }

    public function saveReport(array $report, string $reportType): string
    {
        $timestamp = Carbon::parse($report['generated_at'])->format('Y-m-d_H-i-s');
        $path = storage_path("app/analytics-reports/analytics_report_{$reportType}_{$timestamp}.json");
        $dir = dirname($path);

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($path, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $path;
    }

    public function generateReportFile(array $report, string $format): string
    {
        $timestamp = Carbon::parse($report['generated_at'])->format('Y-m-d_H-i-s');
        $dir = storage_path('app/analytics-reports/tmp');

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return match ($format) {
            'excel' => $this->generateExcelFile($report, $timestamp),
            'csv' => $this->generateCsvFile($dir, $report, $timestamp),
            default => $this->generateJsonFile($dir, $report, $timestamp),
        };
    }

    public function buildSummary(array $report): array
    {
        $overview = $report['overview'];

        return [
            'Sesiones' => number_format($overview['sessions']),
            'Usuarios' => number_format($overview['users']),
            'Vistas de página' => number_format($overview['pageviews']),
            'Tasa de rebote' => round($overview['bounce_rate'] * 100, 1).'%',
            'Período' => $report['period']['start'].' → '.$report['period']['end'],
        ];
    }

    public function calculateNextRun(string $frequency): Carbon
    {
        return match ($frequency) {
            'weekly' => now()->addWeek()->startOfWeek(),
            'monthly' => now()->addMonth()->startOfMonth(),
            default => now()->addDay()->startOfDay(),
        };
    }

    private function generateExcelFile(array $report, string $timestamp): string
    {
        Excel::store(
            new AnalyticsReportExport($report),
            "analytics-reports/tmp/analytics_{$timestamp}.xlsx",
            'local'
        );

        return storage_path("app/analytics-reports/tmp/analytics_{$timestamp}.xlsx");
    }

    private function generateCsvFile(string $dir, array $report, string $timestamp): string
    {
        $path = "{$dir}/analytics_{$timestamp}.csv";
        $overview = $report['overview'];

        $rows = [
            ['Métrica', 'Valor'],
            ['Sesiones', $overview['sessions']],
            ['Usuarios', $overview['users']],
            ['Vistas de página', $overview['pageviews']],
            ['Tasa de rebote', round($overview['bounce_rate'] * 100, 1).'%'],
            ['', ''],
            ['Páginas más visitadas', 'Vistas'],
        ];

        foreach ($report['top_pages'] as $page) {
            $rows[] = [$page['title'], $page['views']];
        }

        $handle = fopen($path, 'w');

        try {
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
        } finally {
            fclose($handle);
        }

        return $path;
    }

    private function generateJsonFile(string $dir, array $report, string $timestamp): string
    {
        $path = "{$dir}/analytics_{$timestamp}.json";
        file_put_contents($path, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $path;
    }
}
