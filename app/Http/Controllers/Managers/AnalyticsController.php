<?php

namespace App\Http\Controllers\Managers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Spatie\Analytics\Facades\Analytics;
use Spatie\Analytics\OrderBy;
use Spatie\Analytics\Period;

class AnalyticsController extends Controller
{
    public function index(Request $request): View
    {
        $range = $request->input('range', 'last_7_days');

        return view('managers.views.analytics.index', compact('range'));
    }

    private function bootAnalytics(): void
    {
        $propertyId = setting('google_analytics_property_id', config('analytics.property_id'));
        if ($propertyId) {
            Analytics::setPropertyId((string) $propertyId);
        }
    }

    public function overview(Request $request): JsonResponse
    {
        $this->bootAnalytics();
        try {
            $range = $request->input('range', 'last_7_days');
            $period = $this->getPeriod($range);

            $rows = Cache::remember("analytics.overview.{$range}", 3600, fn () => Analytics::get(
                period: $period,
                metrics: ['sessions', 'totalUsers', 'screenPageViews', 'bounceRate'],
                dimensions: ['date'],
                maxResults: 400,
                orderBy: [OrderBy::dimension('date', true)],
            ));

            $chartData = $rows->map(fn ($r) => [
                'date' => $this->formatDate($r['date'] ?? ''),
                'sessions' => (int) ($r['sessions'] ?? 0),
                'totalUsers' => (int) ($r['totalUsers'] ?? 0),
                'screenPageViews' => (int) ($r['screenPageViews'] ?? 0),
                'bounceRate' => round((float) ($r['bounceRate'] ?? 0), 4),
            ])->values()->toArray();

            $count = count($chartData);

            return response()->json(['success' => true, 'data' => [
                'chart_data' => $chartData,
                'totals' => [
                    'sessions' => array_sum(array_column($chartData, 'sessions')),
                    'totalUsers' => array_sum(array_column($chartData, 'totalUsers')),
                    'screenPageViews' => array_sum(array_column($chartData, 'screenPageViews')),
                    'bounceRate' => $count > 0
                        ? array_sum(array_column($chartData, 'bounceRate')) / $count
                        : 0.0,
                ],
            ]]);
        } catch (\Exception $e) {
            return $this->errorResponse($e, 'overview');
        }
    }

    public function comparison(Request $request): JsonResponse
    {
        $this->bootAnalytics();
        try {
            $range = $request->input('range', 'last_7_days');
            $prevPeriod = $this->getPreviousPeriod($range);

            $rows = Cache::remember("analytics.comparison.{$range}", 3600, fn () => Analytics::get(
                period: $prevPeriod,
                metrics: ['sessions', 'totalUsers', 'screenPageViews', 'bounceRate'],
                dimensions: ['date'],
                maxResults: 400,
            ));

            $count = $rows->count();

            return response()->json(['success' => true, 'data' => [
                'sessions' => (int) $rows->sum('sessions'),
                'totalUsers' => (int) $rows->sum('totalUsers'),
                'screenPageViews' => (int) $rows->sum('screenPageViews'),
                'bounceRate' => $count > 0 ? $rows->sum('bounceRate') / $count : 0.0,
            ]]);
        } catch (\Exception $e) {
            return $this->errorResponse($e, 'comparison');
        }
    }

    public function sessionMetrics(Request $request): JsonResponse
    {
        $this->bootAnalytics();
        try {
            $range = $request->input('range', 'last_7_days');
            $period = $this->getPeriod($range);

            $rows = Cache::remember("analytics.session_metrics.{$range}", 3600, fn () => Analytics::get(
                period: $period,
                metrics: ['newUsers', 'averageSessionDuration'],
                dimensions: [],
                maxResults: 1,
            ));

            $row = $rows->first() ?? [];

            return response()->json(['success' => true, 'data' => [
                'new_users' => (int) ($row['newUsers'] ?? 0),
                'avg_session_duration' => round((float) ($row['averageSessionDuration'] ?? 0), 1),
            ]]);
        } catch (\Exception $e) {
            return $this->errorResponse($e, 'sessionMetrics');
        }
    }

    public function topPages(Request $request): JsonResponse
    {
        $this->bootAnalytics();
        try {
            $range = $request->input('range', 'last_7_days');
            $period = $this->getPeriod($range);

            $pages = Cache::remember("analytics.top_pages.{$range}", 3600, fn () => Analytics::fetchMostVisitedPages($period, 50));

            return response()->json(['success' => true, 'data' => $pages->map(fn ($p) => [
                'title' => $p['pageTitle'] ?? '-',
                'url' => $p['fullPageUrl'] ?? '#',
                'views' => (int) ($p['screenPageViews'] ?? 0),
            ])->values()->toArray()]);
        } catch (\Exception $e) {
            return $this->errorResponse($e, 'topPages');
        }
    }

    public function topReferrers(Request $request): JsonResponse
    {
        $this->bootAnalytics();
        try {
            $range = $request->input('range', 'last_7_days');
            $period = $this->getPeriod($range);

            $referrers = Cache::remember("analytics.referrers.{$range}", 3600, fn () => Analytics::fetchTopReferrers($period, 30));

            return response()->json(['success' => true, 'data' => $referrers->map(fn ($r) => [
                'url' => $r['pageReferrer'] ?? '-',
                'views' => (int) ($r['screenPageViews'] ?? 0),
            ])->values()->toArray()]);
        } catch (\Exception $e) {
            return $this->errorResponse($e, 'topReferrers');
        }
    }

    public function browsers(Request $request): JsonResponse
    {
        $this->bootAnalytics();
        try {
            $range = $request->input('range', 'last_7_days');
            $period = $this->getPeriod($range);

            $rows = Cache::remember("analytics.browsers.{$range}", 3600, fn () => Analytics::fetchTopBrowsers($period, 10));

            $total = $rows->sum('screenPageViews');

            return response()->json(['success' => true, 'data' => $rows->map(fn ($r) => [
                'name' => $r['browser'] ?? 'Unknown',
                'sessions' => (int) ($r['screenPageViews'] ?? 0),
                'percentage' => $total > 0 ? round(($r['screenPageViews'] / $total) * 100, 1) : 0,
            ])->values()->toArray()]);
        } catch (\Exception $e) {
            return $this->errorResponse($e, 'browsers');
        }
    }

    public function devices(Request $request): JsonResponse
    {
        $this->bootAnalytics();
        try {
            $range = $request->input('range', 'last_7_days');
            $period = $this->getPeriod($range);

            $rows = Cache::remember("analytics.devices.{$range}", 3600, fn () => Analytics::get(
                period: $period,
                metrics: ['sessions'],
                dimensions: ['deviceCategory'],
                maxResults: 10,
                orderBy: [OrderBy::metric('sessions', true)],
            ));

            $total = $rows->sum('sessions');

            return response()->json(['success' => true, 'data' => $rows->map(fn ($r) => [
                'device' => $r['deviceCategory'] ?? 'Unknown',
                'sessions' => (int) ($r['sessions'] ?? 0),
                'percentage' => $total > 0 ? round(($r['sessions'] / $total) * 100, 1) : 0,
            ])->values()->toArray()]);
        } catch (\Exception $e) {
            return $this->errorResponse($e, 'devices');
        }
    }

    public function countries(Request $request): JsonResponse
    {
        $this->bootAnalytics();
        try {
            $range = $request->input('range', 'last_7_days');
            $period = $this->getPeriod($range);

            $rows = Cache::remember("analytics.countries.{$range}", 3600, fn () => Analytics::fetchTopCountries($period, 30));

            $total = $rows->sum('screenPageViews');

            return response()->json(['success' => true, 'data' => $rows->map(fn ($r) => [
                'country' => $r['country'] ?? 'Unknown',
                'sessions' => (int) ($r['screenPageViews'] ?? 0),
                'percentage' => $total > 0 ? round(($r['screenPageViews'] / $total) * 100, 1) : 0,
            ])->values()->toArray()]);
        } catch (\Exception $e) {
            return $this->errorResponse($e, 'countries');
        }
    }

    public function channels(Request $request): JsonResponse
    {
        $this->bootAnalytics();
        try {
            $range = $request->input('range', 'last_7_days');
            $period = $this->getPeriod($range);

            $rows = Cache::remember("analytics.channels.{$range}", 3600, fn () => Analytics::get(
                period: $period,
                metrics: ['sessions'],
                dimensions: ['sessionDefaultChannelGroup'],
                maxResults: 15,
                orderBy: [OrderBy::metric('sessions', true)],
            ));

            $total = $rows->sum('sessions');

            return response()->json(['success' => true, 'data' => $rows->map(fn ($r) => [
                'channel' => $r['sessionDefaultChannelGroup'] ?? 'Other',
                'sessions' => (int) ($r['sessions'] ?? 0),
                'percentage' => $total > 0 ? round(($r['sessions'] / $total) * 100, 1) : 0,
            ])->values()->toArray()]);
        } catch (\Exception $e) {
            return $this->errorResponse($e, 'channels');
        }
    }

    public function realtime(): JsonResponse
    {
        $this->bootAnalytics();
        try {
            $period = Period::create(Carbon::now()->subMinutes(30), Carbon::now());

            $rows = Analytics::getRealtime(
                period: $period,
                metrics: ['activeUsers'],
                dimensions: [],
                maxResults: 1,
            );

            return response()->json(['success' => true, 'data' => [
                'active_users' => (int) ($rows->first()['activeUsers'] ?? 0),
            ]]);
        } catch (\Exception $e) {
            return response()->json(['success' => true, 'data' => ['active_users' => 0]]);
        }
    }

    private function getPeriod(string $range): Period
    {
        return match ($range) {
            'today', 'yesterday' => Period::days(1),
            'last_7_days' => Period::days(7),
            'last_30_days' => Period::days(30),
            'this_month' => Period::create(now()->startOfMonth(), now()),
            'last_month' => Period::create(now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()),
            'this_year' => Period::create(now()->startOfYear(), now()),
            default => Period::days(7),
        };
    }

    private function getPreviousPeriod(string $range): Period
    {
        return match ($range) {
            'today', 'yesterday' => Period::create(now()->subDays(2), now()->subDays(1)),
            'last_7_days' => Period::create(now()->subDays(14), now()->subDays(7)),
            'last_30_days' => Period::create(now()->subDays(60), now()->subDays(30)),
            'this_month' => Period::create(now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()),
            'last_month' => Period::create(now()->subMonths(2)->startOfMonth(), now()->subMonths(2)->endOfMonth()),
            'this_year' => Period::create(now()->subYear()->startOfYear(), now()->subYear()->endOfYear()),
            default => Period::create(now()->subDays(14), now()->subDays(7)),
        };
    }

    private function formatDate(mixed $date): string
    {
        if ($date instanceof Carbon) {
            return $date->format('Ymd');
        }

        return (string) $date;
    }

    private function errorResponse(\Exception $e, string $context): JsonResponse
    {
        Log::error("Analytics error: {$context}", [
            'message' => $e->getMessage(),
            'file' => class_basename($e->getFile()),
            'line' => $e->getLine(),
        ]);

        return response()->json(['success' => false, 'message' => 'Error al obtener datos de Analytics.'], 400);
    }
}
