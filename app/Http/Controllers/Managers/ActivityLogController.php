<?php

namespace App\Http\Controllers\Managers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\BulkActionActivityLogRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Visor de solo lectura del audit trail (Spatie activitylog).
 *
 * Expone los registros de `activity_log` (quién cambió qué y cuándo) con
 * filtros por log, evento, tipo de entidad, autor, texto libre y rango de
 * fechas. El listado por defecto se apoya en el índice `created_at`
 * (ORDER BY DESC). Único dominio del audit trail donde se permite borrar:
 * `bulkAction()`, gateado por `activity.delete` -- pensado para purgar
 * ruido histórico, no para "limpiar" evidencia de forma rutinaria.
 */
class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = $this->filteredQuery($request)
            ->latest()
            ->paginate(paginationNumber(30))
            ->withQueryString();

        // Listas para los filtros: cacheadas porque DISTINCT sobre 1.5M filas es caro
        // y cambian con muy poca frecuencia.
        $logNames = Cache::remember('activitylog.distinct.log_name', 3600, fn () => Activity::query()
            ->select('log_name')->whereNotNull('log_name')->distinct()->orderBy('log_name')->pluck('log_name'));

        $subjectTypes = Cache::remember('activitylog.distinct.subject_type', 3600, fn () => Activity::query()
            ->select('subject_type')->whereNotNull('subject_type')->distinct()->orderBy('subject_type')->pluck('subject_type'));

        $events = Cache::remember('activitylog.distinct.event', 3600, fn () => Activity::query()
            ->select('event')->whereNotNull('event')->distinct()->orderBy('event')->pluck('event'));

        $stats = $this->computeStats();

        $view = request()->ajax() ? 'managers.views.activity._table' : 'managers.views.activity.index';

        return view($view, compact(
            'logs', 'logNames', 'subjectTypes', 'events', 'stats'
        ));
    }

    /** Contadores globales (no respetan los filtros activos): igual que el header de webadmin. */
    public function stats(): JsonResponse
    {
        Cache::forget('activitylog.stats');

        return response()->json($this->computeStats());
    }

    public function export(Request $request): StreamedResponse
    {
        abort_unless($request->user()->can('activity.view'), 403);

        $filename = 'activity-log-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($request) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'id', 'fecha', 'evento', 'modulo', 'autor', 'email_autor',
                'entidad', 'entidad_id', 'descripcion',
            ], ',', '"', '\\');

            $this->filteredQuery($request)
                ->orderBy('id')
                ->chunk(500, function ($chunk) use ($handle) {
                    foreach ($chunk as $log) {
                        fputcsv($handle, [
                            $log->id,
                            optional($log->created_at)->format('Y-m-d H:i:s'),
                            $log->event ?? '',
                            $log->log_name ?? '',
                            $log->causer?->name ?? ($log->causer ? trim(($log->causer->firstname ?? '').' '.($log->causer->lastname ?? '')) : 'Sistema'),
                            $log->causer?->email ?? '',
                            $log->subject_type ? class_basename($log->subject_type) : '',
                            $log->subject_id ?? '',
                            $log->description ?? '',
                        ], ',', '"', '\\');
                    }
                });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function bulkAction(BulkActionActivityLogRequest $request): JsonResponse
    {
        $count = Activity::query()->whereIn('id', $request->input('ids'))->delete();

        Cache::forget('activitylog.stats');

        return response()->json(['success' => true, 'message' => $count.' registro(s) de auditoría eliminados.']);
    }

    /** @return Builder<Activity> */
    private function filteredQuery(Request $request): Builder
    {
        $search = trim((string) $request->input('search', ''));

        return Activity::query()
            ->with('causer')
            ->when($request->filled('log_name'), fn ($q) => $q->where('log_name', $request->input('log_name')))
            ->when($request->filled('event'), fn ($q) => $q->where('event', $request->input('event')))
            ->when($request->filled('subject_type'), fn ($q) => $q->where('subject_type', $request->input('subject_type')))
            ->when($request->filled('causer'), function ($q) use ($request) {
                $term = $request->input('causer');
                $q->whereHasMorph('causer', [User::class], function ($uq) use ($term) {
                    $uq->where('firstname', 'like', "%{$term}%")
                        ->orWhere('lastname', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                });
            })
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date('date_to')))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('description', 'like', "%{$search}%")
                        ->orWhere('properties', 'like', "%{$search}%");
                });
            });
    }

    /** @return array{total:int,created:int,updated:int,deleted:int} */
    private function computeStats(): array
    {
        return Cache::remember('activitylog.stats', 300, fn () => [
            'total' => Activity::query()->count(),
            'created' => Activity::query()->where('event', 'created')->count(),
            'updated' => Activity::query()->where('event', 'updated')->count(),
            'deleted' => Activity::query()->where('event', 'deleted')->count(),
        ]);
    }
}
