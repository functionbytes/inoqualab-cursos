<?php

namespace App\Http\Controllers\Managers\Seo;

use App\Http\Controllers\Controller;
use App\Models\Seo\SeoAlert;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeoAlertsController extends Controller
{
    public function index(): View
    {
        $alerts = SeoAlert::query()
            ->orderByDesc('created_at')
            ->paginate(paginationNumber(30));

        $row = SeoAlert::unacknowledged()->selectRaw('
            COUNT(*) as unacknowledged,
            SUM(CASE WHEN severity = ? THEN 1 ELSE 0 END) as critical,
            SUM(CASE WHEN severity = ? THEN 1 ELSE 0 END) as warning,
            SUM(CASE WHEN severity = ? THEN 1 ELSE 0 END) as info
        ', [SeoAlert::SEVERITY_CRITICAL, SeoAlert::SEVERITY_WARNING, SeoAlert::SEVERITY_INFO])->first();

        $stats = [
            'unacknowledged' => (int) $row->unacknowledged,
            'critical' => (int) $row->critical,
            'warning' => (int) $row->warning,
            'info' => (int) $row->info,
        ];

        $view = request()->ajax() ? 'managers.views.seo.alerts._table' : 'managers.views.seo.alerts.index';

        return view($view, compact('alerts', 'stats'));
    }

    public function acknowledge(SeoAlert $seoAlert): RedirectResponse
    {
        $seoAlert->acknowledge(auth()->id());

        return back()->with('success', 'Alerta marcada como revisada.');
    }

    public function acknowledgeAll(): RedirectResponse
    {
        $count = SeoAlert::unacknowledged()->update([
            'acknowledged_at' => now(),
            'acknowledged_by' => auth()->id(),
        ]);

        return back()->with('success', "{$count} alertas marcadas como revisadas.");
    }

    public function destroy(SeoAlert $seoAlert): RedirectResponse
    {
        $seoAlert->delete();

        return back()->with('success', 'Alerta eliminada.');
    }

    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'action' => ['required', 'in:acknowledge,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:seo_alerts,id'],
        ]);

        $query = SeoAlert::whereIn('id', $request->ids);
        $count = $query->count();

        match ($request->action) {
            'acknowledge' => $query->update(['acknowledged_at' => now(), 'acknowledged_by' => auth()->id()]),
            'delete' => $query->delete(),
        };

        return response()->json(['success' => true, 'message' => $count.' alerta(s) procesadas.']);
    }
}
