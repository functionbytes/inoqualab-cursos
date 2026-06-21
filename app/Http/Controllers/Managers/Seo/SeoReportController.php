<?php

namespace App\Http\Controllers\Managers\Seo;

use App\Http\Controllers\Controller;
use App\Models\Seo\SeoMeta;
use App\Models\Seo\SeoRedirect;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SeoReportController extends Controller
{
    public function index(): View
    {
        $metaRow = SeoMeta::query()->selectRaw('
            COUNT(*) as total,
            AVG(CASE WHEN seo_score IS NOT NULL THEN seo_score END) as avg_score
        ')->first();

        $stats = [
            'total_metas' => (int) $metaRow->total,
            'avg_score' => round($metaRow->avg_score ?? 0, 1),
            'total_redirects' => SeoRedirect::count(),
            'grade_distribution' => SeoMeta::whereNotNull('seo_grade')
                ->selectRaw('seo_grade, COUNT(*) as count')
                ->groupBy('seo_grade')
                ->pluck('count', 'seo_grade')
                ->toArray(),
        ];

        return view('managers.views.seo.report.index', compact('stats'));
    }

    public function export(Request $request): StreamedResponse
    {
        $format = $request->get('format', 'csv');
        $filename = 'seo-report-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($format) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'ID', 'Tipo', 'Modelo', 'Titulo SEO', 'Descripcion', 'Keywords',
                'OG Titulo', 'OG Imagen', 'Twitter Card', 'Canonical URL',
                'Robots', 'Score SEO', 'Grado', 'Ultima auditoria', 'Actualizado',
            ]);

            SeoMeta::with('seoable')->orderBy('seo_score', 'asc')->each(function ($meta) use ($handle) {
                fputcsv($handle, [
                    $meta->id,
                    class_basename($meta->seoable_type ?? ''),
                    $meta->seoable?->title ?? $meta->seoable?->name ?? "#{$meta->seoable_id}",
                    $meta->title ?? '',
                    $meta->description ?? '',
                    $meta->keywords ?? '',
                    $meta->og_title ?? '',
                    $meta->og_image ?? '',
                    $meta->twitter_card ?? '',
                    $meta->canonical_url ?? '',
                    $meta->robots ?? 'index,follow',
                    $meta->seo_score ?? '',
                    $meta->seo_grade ?? '',
                    $meta->seo_audited_at?->format('Y-m-d H:i') ?? '',
                    $meta->updated_at->format('Y-m-d H:i'),
                ]);
            });

            if ($format === 'full') {
                fputcsv($handle, []);
                fputcsv($handle, ['--- REDIRECCIONES ---']);
                fputcsv($handle, ['Source Path', 'Target Path', 'Codigo', 'Activa', 'Hits']);

                SeoRedirect::orderBy('hits_count', 'desc')->each(function ($r) use ($handle) {
                    fputcsv($handle, [
                        $r->source_path,
                        $r->target_path,
                        $r->status_code,
                        $r->is_active ? 'Si' : 'No',
                        $r->hits_count,
                    ]);
                });
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
