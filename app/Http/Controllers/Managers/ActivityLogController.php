<?php

namespace App\Http\Controllers\Managers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

/**
 * Visor de solo lectura del audit trail (Spatie activitylog).
 *
 * Expone los registros de `activity_log` (quién cambió qué y cuándo) con
 * filtros por log, evento, tipo de entidad, autor y rango de fechas.
 * El listado por defecto se apoya en el índice `created_at` (ORDER BY DESC).
 */
class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = Activity::query()
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
            ->latest()
            ->paginate(30)
            ->withQueryString();

        // Listas para los filtros: cacheadas porque DISTINCT sobre 1.5M filas es caro
        // y cambian con muy poca frecuencia.
        $logNames = Cache::remember('activitylog.distinct.log_name', 3600, fn () => Activity::query()
            ->select('log_name')->whereNotNull('log_name')->distinct()->orderBy('log_name')->pluck('log_name'));

        $subjectTypes = Cache::remember('activitylog.distinct.subject_type', 3600, fn () => Activity::query()
            ->select('subject_type')->whereNotNull('subject_type')->distinct()->orderBy('subject_type')->pluck('subject_type'));

        $events = Cache::remember('activitylog.distinct.event', 3600, fn () => Activity::query()
            ->select('event')->whereNotNull('event')->distinct()->orderBy('event')->pluck('event'));

        $stats = Cache::remember('activitylog.stats', 300, fn () => [
            'total' => Activity::query()->count(),
            'today' => Activity::query()->whereDate('created_at', now()->toDateString())->count(),
            'subjects' => Activity::query()->distinct()->count('subject_type'),
        ]);

        return view('managers.views.activity.index', compact(
            'logs', 'logNames', 'subjectTypes', 'events', 'stats'
        ));
    }
}
