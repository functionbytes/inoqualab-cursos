<?php

namespace App\Http\Controllers\Supports\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivitysController extends Controller
{
    public function index(Request $request, $slack)
    {

        $modelSearch = $request->model;
        $propertySearch = $request->property;

        $user = User::slack($slack);

        $models = [
            'Enterprise' => 'Empresas',
            'User' => 'Usuarios',
            'Distributor' => 'Distributidores',
            'Order' => 'Ordenes',
            'Invoice' => 'Facturas',
        ];

        // Antes se ejecutaba ->get() DOS veces sobre activity_log (más de un
        // millón de filas) sin límite, y los filtros de abajo se aplicaban al
        // query DESPUÉS de haberlo ejecutado, así que no tenían ningún efecto.
        $base = Activity::causedBy($user);

        // Contadores por modelo en una sola consulta agregada. El cálculo previo
        // hacía `$activities->has('Enterprise')` sobre una colección plana (no
        // agrupada), de modo que todos los contadores salían siempre 0.
        $grouped = (clone $base)
            ->selectRaw('subject_type, COUNT(*) as total')
            ->groupBy('subject_type')
            ->pluck('total', 'subject_type')
            ->mapWithKeys(fn ($total, $type) => [class_basename((string) $type) => (int) $total]);

        $counts = [];
        foreach ($models as $key => $friendlyName) {
            $counts[$key] = $grouped[$key] ?? 0;
        }

        $query = (clone $base);

        if ($modelSearch) {
            $query->where('subject_type', 'App\\Models\\'.$modelSearch);
        }

        if ($propertySearch) {
            $query->whereNotNull('properties->'.$propertySearch);
        }

        $activities = $query->latest()
            ->paginate(paginationNumber())
            ->withQueryString();

        return view('supports.views.users.activitys.index')->with([
            'user' => $user,
            'activities' => $activities,
            'counts' => $counts,
            'model' => $modelSearch,
            'models' => $models,
        ]);

    }

    public function lists(Request $request)
    {
        $user = User::slack($request->slack);

        $query = Activity::causedBy($user);

        if ($request->model) {
            $query->where('subject_type', 'like', '%'.$request->model.'%');
        }

        // Sin límite, este endpoint AJAX serializaba el historial entero del
        // causer desde una tabla de más de un millón de filas.
        $activities = $query->latest()
            ->limit((int) $request->input('limit', 100))
            ->get();

        return response()->json([
            'success' => true,
            'data' => $activities->map(fn ($a) => [
                'id' => $a->id,
                'description' => $a->description,
                'subject' => class_basename($a->subject_type),
                'created_at' => $a->created_at?->format('d/m/Y H:i'),
                'properties' => $a->properties,
            ]),
        ]);
    }

    public function view($slack)
    {
        $activity = Activity::findOrFail($slack);

        return view('supports.views.users.activitys.view')->with([
            'activity' => $activity,
        ]);
    }
}
