<?php

namespace App\Http\Controllers\Managers\Seo;

use App\Http\Controllers\Controller;
use App\Models\Seo\Seo404Log;
use App\Models\Seo\SeoMeta;
use App\Models\Seo\SeoRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SeoDashboardController extends Controller
{
    public function index(): View
    {
        $totalMetas = SeoMeta::count();
        $withScore = SeoMeta::where('seo_score', '>', 0)->count();

        $indexable = SeoMeta::query()
            ->where(function ($q) {
                $q->whereNull('robots')
                    ->orWhere('robots', '')
                    ->orWhere('robots', 'not like', '%noindex%');
            })
            ->count();

        $sinOgImage = SeoMeta::query()
            ->where(function ($q) {
                $q->whereNull('og_image')->orWhere('og_image', '');
            })
            ->count();

        $avgScore = (int) round((float) SeoMeta::where('seo_score', '>', 0)->avg('seo_score'));

        $metaStats = [
            'total' => $totalMetas,
            'indexable' => $indexable,
            'noindex' => $totalMetas - $indexable,
            'with_score' => $withScore,
            'avg_score' => $avgScore,
            'missing_og_image' => $sinOgImage,
        ];

        $redirectStats = [
            'active' => SeoRedirect::where('is_active', true)->count(),
            'total' => SeoRedirect::count(),
            'total_hits' => (int) SeoRedirect::sum('hits_count'),
        ];

        $total404 = Seo404Log::count();

        $scoreGoal = 70;
        $meetingGoal = $totalMetas > 0
            ? SeoMeta::where('seo_score', '>=', $scoreGoal)->count()
            : 0;
        $goalPercent = $totalMetas > 0
            ? min(100, (int) round(($meetingGoal / $totalMetas) * 100))
            : 0;

        $gradeDistribution = SeoMeta::query()
            ->where('seo_score', '>', 0)
            ->selectRaw('
                SUM(CASE WHEN seo_score >= 90 THEN 1 ELSE 0 END) as A,
                SUM(CASE WHEN seo_score >= 75 AND seo_score < 90 THEN 1 ELSE 0 END) as B,
                SUM(CASE WHEN seo_score >= 60 AND seo_score < 75 THEN 1 ELSE 0 END) as C,
                SUM(CASE WHEN seo_score >= 40 AND seo_score < 60 THEN 1 ELSE 0 END) as D,
                SUM(CASE WHEN seo_score > 0 AND seo_score < 40 THEN 1 ELSE 0 END) as F
            ')
            ->first();

        $worstPages = SeoMeta::query()
            ->where('seo_score', '>', 0)
            ->orderBy('seo_score', 'asc')
            ->limit(10)
            ->get();

        $trend = SeoMeta::query()
            ->select(DB::raw('DATE(updated_at) as date'), DB::raw('COUNT(*) as total'))
            ->where('updated_at', '>=', now()->subDays(7)->startOfDay())
            ->groupBy(DB::raw('DATE(updated_at)'))
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();

        return view('managers.views.seo.dashboard.index', compact(
            'metaStats',
            'redirectStats',
            'total404',
            'goalPercent',
            'meetingGoal',
            'scoreGoal',
            'gradeDistribution',
            'worstPages',
            'trend',
        ));
    }

    public function analytics(Request $request): View
    {
        $search = $request->input('search');

        $pages = SeoMeta::query()
            ->whereNotNull('gsc_clicks')
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('canonical_url', 'like', "%{$search}%");
            }))
            ->orderByDesc('gsc_clicks')
            ->paginate(paginationNumber(20))
            ->withQueryString();

        $gscStats = SeoMeta::query()
            ->whereNotNull('gsc_clicks')
            ->selectRaw('
                SUM(gsc_clicks) as total_clicks,
                SUM(gsc_impressions) as total_impressions,
                AVG(gsc_position) as avg_position,
                COUNT(*) as pages_with_data
            ')
            ->first();

        $lastUpdated = SeoMeta::whereNotNull('gsc_updated_at')->max('gsc_updated_at');

        $view = $request->ajax() ? 'managers.views.seo.dashboard._analytics' : 'managers.views.seo.dashboard.analytics';

        return view($view, compact('pages', 'gscStats', 'lastUpdated'));
    }

    public function showSearchConsoleImport(): View
    {
        return view('managers.views.seo.dashboard.search-console-import');
    }

    public function importSearchConsole(Request $request): RedirectResponse
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $handle = fopen($request->file('csv_file')->getPathname(), 'r');
        $header = fgetcsv($handle);

        if (! $header) {
            fclose($handle);

            return back()->with('error', 'Archivo CSV inválido.');
        }

        $headerMap = array_flip(array_map('strtolower', array_map('trim', $header)));
        $imported = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $pageIndex = $headerMap['page'] ?? $headerMap['top pages'] ?? $headerMap['página'] ?? 0;
            $page = trim($row[$pageIndex] ?? '');
            $clicks = (int) ($row[$headerMap['clicks'] ?? $headerMap['clics'] ?? 1] ?? 0);
            $impressions = (int) ($row[$headerMap['impressions'] ?? $headerMap['impresiones'] ?? 2] ?? 0);
            $position = round((float) ($row[$headerMap['position'] ?? $headerMap['posición'] ?? 4] ?? 0), 1);

            if (empty($page)) {
                $skipped++;

                continue;
            }

            $path = parse_url($page, PHP_URL_PATH);
            $meta = SeoMeta::where('canonical_url', $page)
                ->orWhere('canonical_url', $path)
                ->first();

            if ($meta) {
                $meta->updateQuietly([
                    'gsc_clicks' => $clicks,
                    'gsc_impressions' => $impressions,
                    'gsc_position' => $position,
                    'gsc_updated_at' => now(),
                ]);
                $imported++;
            } else {
                $skipped++;
            }
        }

        fclose($handle);

        return redirect()->route('manager.seo.report.index')
            ->with('success', "Search Console: {$imported} páginas actualizadas, {$skipped} sin coincidencia.");
    }
}
