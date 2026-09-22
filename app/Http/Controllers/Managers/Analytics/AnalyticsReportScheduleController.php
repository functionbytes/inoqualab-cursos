<?php

namespace App\Http\Controllers\Managers\Analytics;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Analytics\StoreScheduleRequest;
use App\Models\AnalyticsReportSchedule;
use App\Services\AnalyticsReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsReportScheduleController extends Controller
{
    public function __construct(private readonly AnalyticsReportService $service) {}

    public function index(Request $request): View
    {
        $search = $request->get('search', '');
        $frequency = $request->get('frequency', '');
        $format = $request->get('format', '');
        $status = $request->get('status', '');

        $query = AnalyticsReportSchedule::query()->orderByDesc('created_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($frequency) {
            $query->where('frequency', $frequency);
        }

        if ($format) {
            $query->where('format', $format);
        }

        if ($status !== '') {
            $query->where('is_active', $status === '1');
        }

        $schedules = $query->paginate(paginationNumber(10))->withQueryString();

        $statsRaw = AnalyticsReportSchedule::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active')
            ->selectRaw('SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive')
            ->selectRaw('SUM(CASE WHEN is_active = 1 AND next_run_at IS NOT NULL AND next_run_at <= ? THEN 1 ELSE 0 END) as pending', [now()->addDays(7)])
            ->first();

        $stats = [
            'total' => (int) $statsRaw->total,
            'active' => (int) $statsRaw->active,
            'inactive' => (int) $statsRaw->inactive,
            'pending' => (int) $statsRaw->pending,
        ];

        $view = request()->ajax() ? 'managers.views.settings.analytics.schedules._table' : 'managers.views.settings.analytics.schedules.index';

        return view($view,
            compact('schedules', 'stats', 'search', 'frequency', 'format', 'status')
        );
    }

    public function create(): View
    {
        return view('managers.views.settings.analytics.schedules.create');
    }

    public function store(StoreScheduleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);
        $data['next_run_at'] = $this->service->calculateNextRun($data['frequency']);

        AnalyticsReportSchedule::create($data);

        return redirect()->route('manager.settings.analytics.schedules.index')
            ->with('success', 'Reporte programado creado correctamente.');
    }

    public function edit(AnalyticsReportSchedule $schedule): View
    {
        return view('managers.views.settings.analytics.schedules.edit', compact('schedule'));
    }

    public function update(StoreScheduleRequest $request, AnalyticsReportSchedule $schedule): RedirectResponse
    {
        $data = $request->validated();

        if ($request->frequency !== $schedule->frequency) {
            $data['next_run_at'] = $this->service->calculateNextRun($data['frequency']);
        }

        $schedule->update($data);

        return redirect()->route('manager.settings.analytics.schedules.index')
            ->with('success', 'Reporte actualizado correctamente.');
    }

    public function destroy(AnalyticsReportSchedule $schedule): RedirectResponse
    {
        abort_unless(auth()->user()->can('analytics.delete'), 403);

        $schedule->delete();

        return redirect()->route('manager.settings.analytics.schedules.index')
            ->with('success', 'Reporte eliminado correctamente.');
    }

    public function toggle(AnalyticsReportSchedule $schedule): JsonResponse
    {
        abort_unless(auth()->user()->can('analytics.update'), 403);

        $schedule->update(['is_active' => ! $schedule->is_active]);

        return response()->json(['is_active' => $schedule->is_active]);
    }

    public function bulkAction(Request $request): JsonResponse
    {
        abort_unless(auth()->user()->can('analytics.update'), 403);

        $request->validate([
            'action' => ['required', 'in:activate,deactivate,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:analytics_report_schedules,id'],
        ]);

        $query = AnalyticsReportSchedule::whereIn('id', $request->ids);
        $count = $query->count();

        match ($request->action) {
            'activate' => $query->update(['is_active' => true]),
            'deactivate' => $query->update(['is_active' => false]),
            'delete' => $query->delete(),
        };

        return response()->json(['success' => true, 'count' => $count]);
    }
}
