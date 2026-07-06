<?php

namespace App\Http\Controllers\Managers\Seo;

use App\Http\Controllers\Controller;
use App\Models\Seo\SeoMeta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SeoMetaController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->input('tab', 'all');
        $sortBy = $request->input('sort_by', 'updated_at');
        $sortDir = $request->input('sort_direction', 'desc');

        $allowedSort = ['updated_at', 'created_at', 'title', 'seo_score', 'seo_grade'];
        if (! in_array($sortBy, $allowedSort)) {
            $sortBy = 'updated_at';
        }

        $query = SeoMeta::query()->with('seoable');

        match ($tab) {
            'indexable' => $query->where(fn ($q) => $q->whereNull('robots')->orWhere('robots', '')->orWhere('robots', 'not like', '%noindex%')),
            'noindex' => $query->where('robots', 'like', '%noindex%'),
            'unoptimized' => $query->where(fn ($q) => $q->whereNull('description')->orWhere('description', '')->orWhereNull('og_image')->orWhere('og_image', '')),
            default => null,
        };

        $query
            ->when($request->input('seoable_type'), fn ($q, $type) => $q->where('seoable_type', $type))
            ->when($request->input('search'), fn ($q, $search) => $q->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            }))
            ->orderBy($sortBy, $sortDir);

        $metas = $query->paginate(20)->withQueryString();

        $stats = SeoMeta::query()->selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN robots NOT LIKE ? THEN 1 ELSE 0 END) as indexable,
            SUM(CASE WHEN robots LIKE ? THEN 1 ELSE 0 END) as noindex,
            SUM(CASE WHEN description IS NULL OR description = ? THEN 1 ELSE 0 END) as missing_description,
            SUM(CASE WHEN og_image IS NULL OR og_image = ? THEN 1 ELSE 0 END) as missing_og_image,
            ROUND(AVG(CASE WHEN seo_score IS NOT NULL THEN seo_score END), 0) as avg_score
        ', ['%noindex%', '%noindex%', '', ''])->first();

        $stats = [
            'total' => (int) ($stats->total ?? 0),
            'indexable' => (int) ($stats->indexable ?? 0),
            'noindex' => (int) ($stats->noindex ?? 0),
            'missing_description' => (int) ($stats->missing_description ?? 0),
            'missing_og_image' => (int) ($stats->missing_og_image ?? 0),
            'avg_score' => (int) ($stats->avg_score ?? 0),
        ];

        $seoableTypes = SeoMeta::query()->select('seoable_type')->distinct()->pluck('seoable_type')->filter()->values()->toArray();

        return view('managers.views.seo.metas.index', compact('metas', 'tab', 'stats', 'seoableTypes'));
    }

    public function edit(SeoMeta $seoMeta): View
    {
        $seoMeta->load('seoable');

        return view('managers.views.seo.metas.edit', compact('seoMeta'));
    }

    public function update(Request $request, SeoMeta $seoMeta): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:70'],
            'description' => ['nullable', 'string', 'max:170'],
            'keywords' => ['nullable', 'string', 'max:500'],
            'og_title' => ['nullable', 'string', 'max:95'],
            'og_description' => ['nullable', 'string', 'max:200'],
            'og_image' => ['nullable', 'url'],
            'og_type' => ['nullable', 'in:website,article,product'],
            'twitter_card' => ['nullable', 'in:summary,summary_large_image'],
            'canonical_url' => ['nullable', 'url'],
            'robots' => ['nullable', 'string', 'max:50'],
            'target_keyword' => ['nullable', 'string', 'max:100'],
            'schema_type' => ['nullable', 'string', 'max:50'],
            'schema_custom' => ['nullable', 'json'],
        ]);

        $seoMeta->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Meta SEO actualizada correctamente.',
        ]);
    }

    public function destroy(SeoMeta $seoMeta): JsonResponse
    {
        $seoMeta->delete();

        return response()->json([
            'success' => true,
            'message' => 'Meta SEO eliminada correctamente.',
        ]);
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:seo_metas,id'],
        ]);

        SeoMeta::query()->whereIn('id', $request->input('ids'))->delete();

        return response()->json([
            'success' => true,
            'message' => 'Registros eliminados correctamente.',
        ]);
    }

    // ── Phase 6: Inline update ────────────────────────────────────────────────────

    public function inlineUpdate(Request $request, SeoMeta $seoMeta): JsonResponse
    {
        $validated = $request->validate([
            'field' => ['required', 'string', 'in:title,description,keywords,robots,canonical_url,target_keyword,og_title,og_description,og_type,twitter_card'],
            'value' => ['nullable', 'string', 'max:500'],
        ]);

        $seoMeta->update([$validated['field'] => $validated['value'] ?: null]);

        return response()->json(['success' => true, 'value' => $seoMeta->{$validated['field']}]);
    }

    // ── Phase 6: Export CSV ───────────────────────────────────────────────────────

    public function export(): StreamedResponse
    {
        $filename = 'seo-metas-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'id', 'seoable_type', 'seoable_id', 'locale', 'title', 'description', 'keywords',
                'og_title', 'og_description', 'og_image', 'og_type',
                'twitter_card', 'twitter_title', 'twitter_description', 'twitter_image',
                'canonical_url', 'robots', 'seo_score', 'seo_grade',
            ], ',', '"', '\\');

            SeoMeta::query()->orderBy('id')->each(function (SeoMeta $meta) use ($handle) {
                fputcsv($handle, [
                    $meta->id,
                    class_basename($meta->seoable_type ?? ''),
                    $meta->seoable_id,
                    $meta->locale ?? '',
                    $meta->title ?? '',
                    $meta->description ?? '',
                    $meta->keywords ?? '',
                    $meta->og_title ?? '',
                    $meta->og_description ?? '',
                    $meta->og_image ?? '',
                    $meta->og_type ?? '',
                    $meta->twitter_card ?? '',
                    $meta->twitter_title ?? '',
                    $meta->twitter_description ?? '',
                    $meta->twitter_image ?? '',
                    $meta->canonical_url ?? '',
                    $meta->robots ?? 'index,follow',
                    $meta->seo_score ?? '',
                    $meta->seo_grade ?? '',
                ], ',', '"', '\\');
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function showImport(): View
    {
        return view('managers.views.seo.metas.import');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
            'update_existing' => ['boolean'],
        ]);

        $handle = fopen($request->file('csv_file')->getPathname(), 'r');

        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $header = fgetcsv($handle);
        $required = ['seoable_type', 'seoable_id'];

        if (! $header || count(array_intersect($required, $header)) < count($required)) {
            fclose($handle);

            return back()->with('error', 'CSV inválido. Se requieren columnas: seoable_type, seoable_id');
        }

        $map = array_flip($header);
        $updated = $created = $skipped = $errors = 0;
        $updateExisting = $request->boolean('update_existing', false);

        DB::transaction(function () use ($handle, $map, $updateExisting, &$updated, &$created, &$skipped, &$errors) {
            $updatableFields = ['title', 'description', 'keywords', 'og_title', 'og_description',
                'og_image', 'og_type', 'twitter_card', 'twitter_title', 'twitter_description',
                'twitter_image', 'canonical_url', 'robots'];

            // Build type map from all registered Eloquent models
            $typeMap = [];
            foreach (get_declared_classes() as $class) {
                if (is_subclass_of($class, Model::class)) {
                    $typeMap[class_basename($class)] = $class;
                }
            }

            while (($row = fgetcsv($handle)) !== false) {
                try {
                    $shortType = trim($row[$map['seoable_type']] ?? '');
                    $seoableId = (int) ($row[$map['seoable_id']] ?? 0);

                    if (! $shortType || ! $seoableId) {
                        $errors++;

                        continue;
                    }

                    $seoableType = $typeMap[$shortType] ?? null;
                    if (! $seoableType) {
                        $errors++;

                        continue;
                    }

                    $existing = SeoMeta::where('seoable_type', $seoableType)
                        ->where('seoable_id', $seoableId)
                        ->first();

                    if ($existing && ! $updateExisting) {
                        $skipped++;

                        continue;
                    }

                    $data = ['seoable_type' => $seoableType, 'seoable_id' => $seoableId];
                    foreach ($updatableFields as $field) {
                        if (isset($map[$field]) && isset($row[$map[$field]]) && $row[$map[$field]] !== '') {
                            $data[$field] = $row[$map[$field]];
                        }
                    }

                    if ($existing) {
                        $existing->update($data);
                        $updated++;
                    } else {
                        SeoMeta::create($data);
                        $created++;
                    }
                } catch (\Throwable) {
                    $errors++;
                }
            }
        });

        fclose($handle);

        return redirect()->route('manager.seo.metas.index')
            ->with('success', "Importación: {$created} creados, {$updated} actualizados, {$skipped} omitidos, {$errors} errores.");
    }

    // ── Phase 6: Export JSON ──────────────────────────────────────────────────────

    public function exportJson(): StreamedResponse
    {
        $filename = 'seo-backup-'.now()->format('Y-m-d').'.json';

        return response()->streamDownload(function () {
            echo "{\n";
            echo '  "exported_at": '.json_encode(now()->toIso8601String()).",\n";
            echo "  \"version\": \"1.0\",\n";
            echo "  \"metas\": [\n";
            $first = true;
            SeoMeta::query()->chunk(500, function ($chunk) use (&$first) {
                foreach ($chunk as $meta) {
                    if (! $first) {
                        echo ",\n";
                    }
                    echo '    '.json_encode($meta->toArray());
                    $first = false;
                }
            });
            echo "\n  ]\n";
            echo "}\n";
        }, $filename, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function showImportJson(): View
    {
        return view('managers.views.seo.metas.import-json');
    }

    public function importJson(Request $request): RedirectResponse
    {
        $request->validate([
            'json_file' => ['required', 'file', 'mimes:json,txt', 'max:51200'],
            'skip_existing' => ['boolean'],
        ]);

        $content = file_get_contents($request->file('json_file')->getPathname());
        $data = json_decode($content, true);

        if (! $data || ! isset($data['version'])) {
            return back()->with('error', 'Archivo JSON inválido o formato no reconocido.');
        }

        $imported = 0;
        $skipExisting = $request->boolean('skip_existing', true);

        DB::transaction(function () use ($data, &$imported, $skipExisting) {
            foreach ($data['metas'] ?? [] as $row) {
                if (empty($row['seoable_type']) || empty($row['seoable_id'])) {
                    continue;
                }
                if ($skipExisting && SeoMeta::where('seoable_type', $row['seoable_type'])
                    ->where('seoable_id', $row['seoable_id'])->exists()) {
                    continue;
                }
                SeoMeta::updateOrCreate(
                    ['seoable_type' => $row['seoable_type'], 'seoable_id' => $row['seoable_id']],
                    array_intersect_key($row, array_flip([
                        'locale', 'title', 'description', 'keywords', 'og_title', 'og_description',
                        'og_image', 'og_type', 'twitter_card', 'canonical_url', 'robots', 'schema_type',
                    ]))
                );
                $imported++;
            }
        });

        return redirect()->route('manager.seo.metas.index')
            ->with('success', "Importación JSON: {$imported} metas importados.");
    }

    // ── Phase 6: Keyword suggestions ─────────────────────────────────────────────

    public function keywordSuggestions(Request $request): JsonResponse
    {
        $keyword = $request->validate(['q' => 'required|string|min:2|max:100'])['q'];

        $related = SeoMeta::where(function ($q) use ($keyword) {
            $q->where('title', 'like', "%{$keyword}%")
                ->orWhere('target_keyword', 'like', "%{$keyword}%")
                ->orWhere('keywords', 'like', "%{$keyword}%");
        })
            ->whereNotNull('gsc_clicks')
            ->orderByDesc('gsc_clicks')
            ->limit(10)
            ->get(['title', 'target_keyword', 'keywords', 'gsc_clicks', 'gsc_position']);

        $suggestions = collect();

        foreach ($related as $meta) {
            if ($meta->target_keyword && stripos($meta->target_keyword, $keyword) !== false) {
                $suggestions->push([
                    'keyword' => $meta->target_keyword,
                    'source' => 'target_keyword',
                    'clicks' => $meta->gsc_clicks,
                    'position' => $meta->gsc_position,
                ]);
            }

            if ($meta->keywords) {
                foreach (explode(',', $meta->keywords) as $kw) {
                    $kw = trim($kw);
                    if (strlen($kw) > 2 && stripos($kw, $keyword) !== false) {
                        $suggestions->push([
                            'keyword' => $kw,
                            'source' => 'keywords',
                            'clicks' => $meta->gsc_clicks,
                            'position' => $meta->gsc_position,
                        ]);
                    }
                }
            }
        }

        $variations = [
            'cómo '.$keyword,
            $keyword.' online',
            'mejor '.$keyword,
            $keyword.' precio',
            'aprender '.$keyword,
        ];

        return response()->json([
            'suggestions' => $suggestions->unique('keyword')->take(10)->values(),
            'variations' => $variations,
        ]);
    }

    // ── Phase 10: Hreflang y multiidioma ─────────────────────────────────────────

    public function hreflangIndex(): View
    {
        $metas = SeoMeta::whereNotNull('locale')
            ->where('locale', '!=', '')
            ->orderBy('locale')
            ->get(['id', 'title', 'canonical_url', 'locale', 'seoable_type', 'seoable_id']);

        $grouped = $metas->groupBy('locale');

        $conflicts = [];
        $byPath = $metas->groupBy(fn ($m) => parse_url($m->canonical_url ?? '', PHP_URL_PATH));

        foreach ($byPath as $path => $group) {
            if ($group->count() > 1) {
                $locales = $group->pluck('locale')->unique();
                if ($locales->count() < $group->count()) {
                    $conflicts[] = ['path' => $path, 'count' => $group->count()];
                }
            }
        }

        $withoutLocale = SeoMeta::whereNull('locale')->orWhere('locale', '')->count();

        return view('managers.views.seo.metas.hreflang', compact('metas', 'grouped', 'conflicts', 'withoutLocale'));
    }

    public function translateMeta(Request $request, SeoMeta $seoMeta): JsonResponse
    {
        $validated = $request->validate([
            'fields' => ['required', 'array'],
            'fields.*' => ['string', 'in:title,description,og_title,og_description,keywords'],
            'target_lang' => ['required', 'string', 'max:10'],
        ]);

        if (! class_exists('DeepL\Translator')) {
            return response()->json(['error' => 'DeepL SDK no instalado. Ejecuta: composer require deeplcom/deepl-php'], 422);
        }

        $apiKey = config('services.deepl.key', env('DEEPL_API_KEY', ''));
        if (empty($apiKey)) {
            return response()->json(['error' => 'DeepL API key no configurada. Establece DEEPL_API_KEY en .env'], 422);
        }

        $translatorClass = 'DeepL\Translator';
        $translator = new $translatorClass($apiKey);
        $results = [];

        foreach ($validated['fields'] as $field) {
            $originalText = $seoMeta->{$field} ?? '';
            if (empty($originalText)) {
                continue;
            }

            try {
                $result = $translator->translateText($originalText, null, $validated['target_lang']);
                $results[$field] = $result->text;
            } catch (\Throwable $e) {
                Log::error('SEO meta translation failed', ['error' => $e->getMessage()]);

                return response()->json(['error' => 'Error al traducir. Por favor, inténtalo de nuevo.'], 422);
            }
        }

        return response()->json([
            'translations' => $results,
            'target_lang' => $validated['target_lang'],
            'message' => 'Traducción completada. Revisa y guarda los cambios.',
        ]);
    }

    public function createLocale(Request $request, SeoMeta $seoMeta): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', 'max:10', 'regex:/^[a-z]{2}(-[A-Z]{2})?$/'],
        ]);

        $locale = $validated['locale'];

        $existing = SeoMeta::where('seoable_type', $seoMeta->seoable_type)
            ->where('seoable_id', $seoMeta->seoable_id)
            ->where('locale', $locale)
            ->first();

        if ($existing) {
            return redirect()->route('manager.seo.metas.edit', $existing)
                ->with('info', 'Ya existe un meta para este idioma.');
        }

        $newMeta = SeoMeta::create([
            'seoable_type' => $seoMeta->seoable_type,
            'seoable_id' => $seoMeta->seoable_id,
            'locale' => $locale,
            'robots' => 'index,follow',
        ]);

        return redirect()->route('manager.seo.metas.edit', $newMeta)
            ->with('success', 'Meta SEO creado para el idioma '.strtoupper($locale).'.');
    }
}
