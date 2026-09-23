<?php

namespace App\Http\Controllers\Managers\Seo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Seo\BulkActionSeoMetaRequest;
use App\Http\Requests\Managers\Seo\ImportSeoMetaJsonRequest;
use App\Http\Requests\Managers\Seo\ImportSeoMetaRequest;
use App\Http\Requests\Managers\Seo\InlineUpdateSeoMetaRequest;
use App\Http\Requests\Managers\Seo\UpdateSeoMetaRequest;
use App\Models\Blog\Blog;
use App\Models\Bundle\Bundle;
use App\Models\Certifier;
use App\Models\Course\Course;
use App\Models\Instruction\Instruction;
use App\Models\Seo\SeoMeta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $metas = $query->paginate(paginationNumber(20))->withQueryString();

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

        $view = request()->ajax() ? 'managers.views.seo.metas._table' : 'managers.views.seo.metas.index';

        return view($view, compact('metas', 'tab', 'stats', 'seoableTypes'));
    }

    public function edit(SeoMeta $seoMeta): View
    {
        $seoMeta->load('seoable');

        return view('managers.views.seo.metas.edit', compact('seoMeta'));
    }

    public function update(UpdateSeoMetaRequest $request, SeoMeta $seoMeta): JsonResponse
    {
        $validated = $request->validated();

        // schema_custom llega como string JSON (regla 'json' solo valida sintaxis,
        // no decodifica). El modelo castea el atributo a 'array', así que hay que
        // decodificarlo antes de asignar o Eloquent lo vuelve a codificar sobre el
        // string ya codificado (doble-encoding) -- mismo patrón ya usado en
        // SchemaOrgController::update().
        if (array_key_exists('schema_custom', $validated)) {
            $validated['schema_custom'] = $validated['schema_custom'] !== null
                ? json_decode($validated['schema_custom'], true)
                : null;
        }

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

    public function bulkAction(BulkActionSeoMetaRequest $request): JsonResponse
    {
        $count = SeoMeta::query()->whereIn('id', $request->input('ids'))->count();
        SeoMeta::query()->whereIn('id', $request->input('ids'))->delete();

        return response()->json([
            'success' => true,
            'message' => $count.' registro(s) meta SEO eliminado(s) correctamente.',
        ]);
    }

    // ── Phase 6: Inline update ────────────────────────────────────────────────────

    public function inlineUpdate(InlineUpdateSeoMetaRequest $request, SeoMeta $seoMeta): JsonResponse
    {
        $validated = $request->validated();

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

    public function import(ImportSeoMetaRequest $request): RedirectResponse
    {
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

            // get_declared_classes() solo devuelve clases ya autocargadas en
            // ESTA request -- no determinista, dependía de qué otro código
            // se hubiera ejecutado antes por casualidad. Whitelist explícita
            // (mismos 5 modelos que usan HasSeo), igual que SeoOrphanController.
            $typeMap = [
                'Course' => Course::class,
                'Blog' => Blog::class,
                'Bundle' => Bundle::class,
                'Instruction' => Instruction::class,
                'Certifier' => Certifier::class,
            ];

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

    public function importJson(ImportSeoMetaJsonRequest $request): RedirectResponse
    {
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
}
