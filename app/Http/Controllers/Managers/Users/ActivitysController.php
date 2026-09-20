<?php

namespace App\Http\Controllers\Managers\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class ActivitysController extends Controller
{
    /**
     * Mismo guard que Managers\Users\UsersController::guardNotSuperadmin():
     * un manager no-superadmin no puede ver la actividad de una cuenta
     * superadmin (este endpoint no distinguia el rol del usuario objetivo).
     */
    private function guardNotSuperadmin(User $user): void
    {
        abort_if(
            $user->role === 'superadmin' && auth()->user()->role !== 'superadmin',
            403,
            'No tienes autorización para gestionar esta cuenta.'
        );
    }

    public function index(Request $request, $slack)
    {

        $modelSearch = $request->model;
        $propertySearch = $request->property;

        $user = User::slack($slack);
        $this->guardNotSuperadmin($user);

        $query = Activity::causedBy($user);

        $models = [
            'Enterprise' => 'Empresas',
            'User' => 'Usuarios',
            'Distributor' => 'Distributidores',
            'Order' => 'Ordenes',
            'Invoice' => 'Facturas',
        ];

        // Counts por tipo vía SQL GROUP BY — evita cargar todos los registros en PHP.
        $countsRaw = Activity::causedBy($user)
            ->select(DB::raw('subject_type, COUNT(*) as total'))
            ->groupBy('subject_type')
            ->pluck('total', 'subject_type');

        $counts = [];
        foreach ($models as $key => $friendlyName) {
            $counts[$key] = $countsRaw
                ->filter(fn ($v, $k) => str_contains($k, $key))
                ->sum();
        }

        if ($modelSearch) {
            $query->where('subject_type', 'like', '%'.$modelSearch.'%');
        }

        $activities = $query->orderBy('created_at', 'desc')->paginate(paginationNumber());

        return view('managers.views.users.activitys.index')->with([
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
        $this->guardNotSuperadmin($user);

        $query = Activity::causedBy($user);

        if ($request->model) {
            $query->where('subject_type', 'like', '%'.$request->model.'%');
        }

        $activities = $query->orderBy('created_at', 'desc')->limit(200)->get();

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

        return view('managers.views.users.activitys.view')->with([
            'activity' => $activity,
        ]);
    }
}
