<?php

namespace App\Http\Controllers\Managers\Seo;

use App\Http\Controllers\Controller;
use App\Models\Seo\Seo404Log;
use App\Models\Seo\SeoRedirect;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class Seo404LogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = Seo404Log::query()
            ->when(
                $request->filled('search'),
                fn ($q) => $q->where('path', 'like', '%'.$request->input('search').'%')
            )
            ->when(
                $request->filled('has_redirect'),
                fn ($q) => $q->where('has_redirect', $request->boolean('has_redirect'))
            )
            ->orderByHits()
            ->paginate(paginationNumber(30))
            ->withQueryString();

        $stats = [
            'total' => Seo404Log::count(),
            'unresolved' => Seo404Log::where('has_redirect', false)->count(),
            'resolved' => Seo404Log::where('has_redirect', true)->count(),
        ];

        $view = request()->ajax() ? 'managers.views.seo.logs._table' : 'managers.views.seo.logs.index';

        return view($view, compact('logs', 'stats'));
    }

    public function createRedirect(Request $request): JsonResponse
    {

        $validated = $request->validate([
            'log_id' => ['required', 'integer', 'exists:seo_404_logs,id'],
            'target_path' => ['required', 'string', 'max:500'],
            'status_code' => ['nullable', 'in:301,302'],
        ]);

        $log = Seo404Log::findOrFail($validated['log_id']);

        // Ambas escrituras son atómicas: o se crea el redirect y se marca el
        // log, o no se hace nada (evita redirects huérfanos sin marcar el 404).
        DB::transaction(function () use ($log, $validated) {
            SeoRedirect::create([
                'source_path' => $log->path,
                'target_path' => $validated['target_path'],
                'status_code' => $validated['status_code'] ?? 301,
                'is_active' => true,
            ]);

            $log->update(['has_redirect' => true]);
        });

        SeoRedirect::clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Redirección creada correctamente desde el error 404.',
        ]);
    }

    public function markResolved(Seo404Log $seo404Log): JsonResponse
    {

        $seo404Log->delete();

        return response()->json([
            'success' => true,
            'message' => 'Registro eliminado correctamente.',
        ]);
    }

    public function bulkDestroy(Request $request): JsonResponse
    {

        $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:seo_404_logs,id'],
        ]);

        Seo404Log::query()->whereIn('id', $request->input('ids'))->delete();

        return response()->json([
            'success' => true,
            'message' => 'Registros eliminados correctamente.',
        ]);
    }

    public function clear(Request $request): JsonResponse
    {

        $request->validate([
            'all' => ['boolean'],
        ]);

        if ($request->boolean('all')) {
            Seo404Log::query()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Todos los registros han sido eliminados.',
            ]);
        }

        $cutoff = now()->subDays(90);
        Seo404Log::query()->where('last_seen_at', '<', $cutoff)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Registros más antiguos de 90 días eliminados correctamente.',
        ]);
    }
}
