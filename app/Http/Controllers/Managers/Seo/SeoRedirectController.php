<?php

namespace App\Http\Controllers\Managers\Seo;

use App\Http\Controllers\Controller;
use App\Models\Seo\SeoRedirect;
use App\Models\Seo\SeoRedirectHit;
use App\Services\RedirectChainDetector;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SeoRedirectController extends Controller
{
    public function index(Request $request): View
    {
        $redirects = SeoRedirect::query()
            ->when($request->input('search'), fn ($q, $search) => $q->search($search))
            ->when($request->input('is_active') !== null, fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('managers.views.seo.redirects.index', compact('redirects'));
    }

    public function create(): View
    {
        return view('managers.views.seo.redirects.create');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'source_path' => ['required', 'string', 'max:500'],
            'target_path' => ['required', 'string', 'max:500'],
            'status_code' => ['required', 'in:301,302'],
            'is_regex' => ['boolean'],
            'is_wildcard' => ['boolean'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        SeoRedirect::create($validated);
        SeoRedirect::clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Redirección creada correctamente.',
        ]);
    }

    public function edit(SeoRedirect $seoRedirect): View
    {
        return view('managers.views.seo.redirects.edit', compact('seoRedirect'));
    }

    public function update(Request $request, SeoRedirect $seoRedirect): JsonResponse
    {
        $validated = $request->validate([
            'source_path' => ['required', 'string', 'max:500'],
            'target_path' => ['required', 'string', 'max:500'],
            'status_code' => ['required', 'in:301,302'],
            'is_regex' => ['boolean'],
            'is_wildcard' => ['boolean'],
            'is_active' => ['boolean'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $seoRedirect->update($validated);
        SeoRedirect::clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Redirección actualizada correctamente.',
        ]);
    }

    public function destroy(SeoRedirect $seoRedirect): JsonResponse
    {
        $seoRedirect->delete();
        SeoRedirect::clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Redirección eliminada correctamente.',
        ]);
    }

    public function toggleActive(SeoRedirect $seoRedirect): JsonResponse
    {
        $seoRedirect->update(['is_active' => ! $seoRedirect->is_active]);
        SeoRedirect::clearCache();

        $state = $seoRedirect->is_active ? 'activada' : 'desactivada';

        return response()->json([
            'success' => true,
            'message' => "Redirección {$state} correctamente.",
            'is_active' => $seoRedirect->is_active,
        ]);
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:seo_redirects,id'],
        ]);

        SeoRedirect::query()->whereIn('id', $request->input('ids'))->delete();
        SeoRedirect::clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Redirecciones eliminadas correctamente.',
        ]);
    }

    // ── Phase 5: Análisis avanzado ────────────────────────────────────────────────

    public function test(SeoRedirect $seoRedirect): JsonResponse
    {
        try {
            $response = Http::withoutRedirecting()->timeout(5)->get(url($seoRedirect->source_path));

            return response()->json([
                'status' => $response->status(),
                'expected' => $seoRedirect->status_code,
                'matches' => $response->status() === $seoRedirect->status_code,
                'target' => $seoRedirect->target_path,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Redirect test failed', ['id' => $seoRedirect->id, 'error' => $e->getMessage()]);

            return response()->json(['error' => 'No se pudo realizar la petición de prueba.'], 422);
        }
    }

    public function analytics(SeoRedirect $seoRedirect): JsonResponse
    {
        $hits = SeoRedirectHit::where('seo_redirect_id', $seoRedirect->id)
            ->where('hit_date', '>=', now()->subDays(30)->toDateString())
            ->orderBy('hit_date')
            ->get(['hit_date', 'hit_count']);

        $dateRange = collect();
        for ($i = 29; $i >= 0; $i--) {
            $dateRange[now()->subDays($i)->toDateString()] = 0;
        }

        foreach ($hits as $hit) {
            $dateRange[$hit->hit_date->toDateString()] = $hit->hit_count;
        }

        return response()->json([
            'labels' => $dateRange->keys()->toArray(),
            'data' => $dateRange->values()->toArray(),
            'total_30d' => $hits->sum('hit_count'),
            'redirect' => [
                'source_path' => $seoRedirect->source_path,
                'target_path' => $seoRedirect->target_path,
                'hits_count' => $seoRedirect->hits_count,
            ],
        ]);
    }

    public function detectChains(): JsonResponse
    {
        $chains = (new RedirectChainDetector)->detectAll();

        return response()->json([
            'chains' => $chains->toArray(),
            'count' => $chains->count(),
        ]);
    }

    public function resolveChains(): JsonResponse
    {
        $updated = (new RedirectChainDetector)->resolveAll();
        SeoRedirect::clearCache();

        return response()->json([
            'success' => true,
            'updated' => $updated,
            'message' => "Se aplanaron {$updated} cadenas de redirects.",
        ]);
    }

    public function showHtaccessImport(): View
    {
        return view('managers.views.seo.redirects.htaccess-import');
    }

    public function importHtaccess(Request $request): RedirectResponse
    {
        $request->validate([
            'htaccess_content' => ['required', 'string', 'max:100000'],
        ]);

        $content = $request->input('htaccess_content');
        $skipExisting = $request->boolean('skip_existing', true);
        $lines = explode("\n", $content);
        $imported = 0;
        $skipped = 0;
        $errors = 0;

        DB::beginTransaction();
        try {
            foreach ($lines as $line) {
                $line = trim($line);

                if (empty($line) || str_starts_with($line, '#')) {
                    continue;
                }

                $sourcePath = $targetPath = null;
                $statusCode = 301;

                if (preg_match('/^Redirect\s+(\d{3})\s+(\S+)\s+(\S+)/i', $line, $m)) {
                    $statusCode = (int) $m[1];
                    $sourcePath = $m[2];
                    $targetPath = $m[3];
                } elseif (preg_match('/^RedirectPermanent\s+(\S+)\s+(\S+)/i', $line, $m)) {
                    $sourcePath = $m[1];
                    $targetPath = $m[2];
                    $statusCode = 301;
                } elseif (preg_match('/^RewriteRule\s+(\S+)\s+(\S+)\s+\[([^\]]+)\]/i', $line, $m)) {
                    $flags = $m[3];
                    if (! str_contains(strtolower($flags), 'r=')) {
                        continue;
                    }
                    preg_match('/r=(\d{3})/i', $flags, $statusMatch);
                    $statusCode = (int) ($statusMatch[1] ?? 301);
                    $sourcePath = '/'.ltrim($m[1], '^');
                    $sourcePath = rtrim($sourcePath, '$');
                    $targetPath = $m[2];
                } else {
                    continue;
                }

                if (! $sourcePath || ! $targetPath || ! in_array($statusCode, [301, 302, 307, 308])) {
                    $errors++;

                    continue;
                }

                if ($skipExisting && SeoRedirect::where('source_path', $sourcePath)->exists()) {
                    $skipped++;

                    continue;
                }

                SeoRedirect::updateOrCreate(
                    ['source_path' => $sourcePath],
                    ['target_path' => $targetPath, 'status_code' => $statusCode, 'is_active' => true]
                );
                $imported++;
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('SEO htaccess import failed', ['error' => $e->getMessage()]);

            return back()->with('error', 'Error durante la importación.');
        }

        SeoRedirect::clearCache();

        return redirect()->route('manager.seo.redirects.index')
            ->with('success', "Importación .htaccess: {$imported} importados, {$skipped} omitidos, {$errors} errores.");
    }

    public function export(): StreamedResponse
    {
        $filename = 'redirects-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['source_path', 'target_path', 'status_code', 'is_active', 'hits_count']);

            SeoRedirect::query()->orderBy('source_path')->each(function (SeoRedirect $r) use ($handle) {
                fputcsv($handle, [
                    $r->source_path,
                    $r->target_path,
                    $r->status_code,
                    $r->is_active ? '1' : '0',
                    $r->hits_count,
                ]);
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
