<?php

namespace App\Http\Controllers\Managers\Seo;

use App\Http\Controllers\Controller;
use App\Models\Seo\SeoAuditLog;
use App\Models\Seo\SeoMeta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SeoAuditHistoryController extends Controller
{
    public function index(): View
    {
        $search = request('search');
        $grade = request('grade');

        $logs = SeoAuditLog::query()
            ->with('seoMeta')
            ->when($search, fn ($q) => $q->where('url', 'like', "%{$search}%"))
            ->when($grade, fn ($q) => $q->where('grade', $grade))
            ->latest('audited_at')
            ->paginate(25)
            ->withQueryString();

        $row = SeoAuditLog::query()->selectRaw('
            COUNT(*) as total_audits,
            AVG(score) as avg_score,
            SUM(CASE WHEN grade = ? THEN 1 ELSE 0 END) as grade_a,
            SUM(CASE WHEN grade = ? THEN 1 ELSE 0 END) as grade_f
        ', ['A', 'F'])->first();

        $stats = [
            'total_audits' => (int) $row->total_audits,
            'avg_score' => round($row->avg_score ?? 0, 1),
            'grade_a' => (int) $row->grade_a,
            'grade_f' => (int) $row->grade_f,
        ];

        return view('managers.views.seo.audit.history', compact('logs', 'stats'));
    }

    public function forMeta(SeoMeta $seoMeta): View
    {
        $logs = SeoAuditLog::query()
            ->where('seo_meta_id', $seoMeta->id)
            ->latest('audited_at')
            ->paginate(20);

        return view('managers.views.seo.audit.meta-history', compact('logs', 'seoMeta'));
    }

    public function forMetaJson(SeoMeta $seoMeta): JsonResponse
    {
        $logs = SeoAuditLog::forMeta($seoMeta->id)
            ->orderByDesc('audited_at')
            ->limit(10)
            ->get(['id', 'score', 'grade', 'issues', 'audited_at']);

        return response()->json($logs);
    }

    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'action' => ['required', 'string', Rule::in(['delete'])],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:seo_audit_logs,id'],
        ]);

        $count = SeoAuditLog::whereIn('id', $request->input('ids'))->delete();

        return response()->json(['success' => true, 'count' => $count, 'message' => $count.' entrada(s) eliminadas.']);
    }

    public function clear(): JsonResponse
    {
        SeoAuditLog::truncate();

        return response()->json(['success' => true, 'message' => 'Historial de auditorías eliminado correctamente.']);
    }

    public function destroy(SeoAuditLog $seoAuditLog): RedirectResponse
    {
        $seoAuditLog->delete();

        return back()->with('success', 'Registro eliminado.');
    }
}
