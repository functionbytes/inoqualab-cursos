<?php

namespace App\Http\Controllers\Managers\Seo;

use App\Http\Controllers\Controller;
use App\Models\Seo\SeoWebVital;
use Illuminate\View\View;

class WebVitalsController extends Controller
{
    public function index(): View
    {
        $since = now()->subDays(28);

        $globalP75 = $this->computeP75(null, $since);

        $worstPages = SeoWebVital::query()
            ->selectRaw('url_path, metric, COUNT(*) as samples, AVG(value) as avg_value')
            ->where('captured_at', '>=', $since)
            ->where('rating', 'poor')
            ->groupBy('url_path', 'metric')
            ->havingRaw('COUNT(*) >= 3')
            ->orderByDesc('samples')
            ->limit(20)
            ->get();

        $byDevice = SeoWebVital::query()
            ->selectRaw('device, metric, AVG(value) as avg_value, COUNT(*) as samples')
            ->where('captured_at', '>=', $since)
            ->groupBy('device', 'metric')
            ->get();

        $thresholds = SeoWebVital::THRESHOLDS;
        $totalSamples = SeoWebVital::where('captured_at', '>=', $since)->count();

        return view('managers.views.seo.web-vitals.index', compact(
            'globalP75', 'worstPages', 'byDevice', 'thresholds', 'totalSamples', 'since'
        ));
    }

    public function show(string $path): View
    {
        $since = now()->subDays(28);
        $normalizedPath = '/'.ltrim($path, '/');

        $p75 = $this->computeP75($normalizedPath, $since);

        $trend = SeoWebVital::query()
            ->selectRaw('DATE(captured_at) as date, metric, COUNT(*) as samples, AVG(value) as avg_value')
            ->where('url_path', $normalizedPath)
            ->where('captured_at', '>=', $since)
            ->groupBy('date', 'metric')
            ->orderBy('date')
            ->get();

        $thresholds = SeoWebVital::THRESHOLDS;

        return view('managers.views.seo.web-vitals.show', compact(
            'normalizedPath', 'p75', 'trend', 'thresholds', 'since'
        ));
    }

    private function computeP75(?string $path, \DateTimeInterface $since): array
    {
        $days = max(1, (int) now()->diffInDays($since, true));
        $results = [];

        foreach (array_keys(SeoWebVital::THRESHOLDS) as $metric) {
            $query = SeoWebVital::query()->forMetric($metric)->since($since);

            if ($path) {
                $query->forUrlPath($path);
            }

            $count = (int) $query->count();

            if ($count === 0) {
                $results[$metric] = ['value' => 0.0, 'rating' => 'unknown', 'samples' => 0];

                continue;
            }

            $p75 = SeoWebVital::p75($metric, $path, $days) ?? 0.0;
            $results[$metric] = [
                'value' => round($p75, 3),
                'rating' => SeoWebVital::rate($metric, $p75),
                'samples' => $count,
            ];
        }

        return $results;
    }
}
