<?php

namespace App\Http\Controllers\Supports\Distributors;

use App\Exports\Distributors\StaffExport;
use App\Http\Controllers\Concerns\RestrictsManageableUsers;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Supports\Concerns\ValidatesUniqueUserFields;
use App\Models\Distributor\Distributor;
use App\Models\Distributor\DistributorStaff;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class StaffController extends Controller
{
    use RestrictsManageableUsers, ValidatesUniqueUserFields;

    public function index(Request $request, $slack)
    {
        $distributor = Distributor::slack($slack);
        $searchKey = $request->search;
        $available = $request->available;

        $users = $distributor->staffs()->orderBy('updated_at', 'desc');

        if ($searchKey) {
            $users = $users->where(function ($query) use ($searchKey) {
                $query->where('users.firstname', 'like', '%'.$searchKey.'%')
                    ->orWhere('users.lastname', 'like', '%'.$searchKey.'%')
                    ->orWhere(DB::raw("CONCAT(users.firstname, ' ', users.lastname)"), 'like', '%'.$searchKey.'%')
                    ->orWhere('users.email', 'like', '%'.$searchKey.'%')->orWhere('users.identification', 'like', '%'.$searchKey.'%');
            });
        }

        if ($available != null) {
            $users = $users->where('users.available', $available);
        }

        $users = $users->paginate(paginationNumber());

        // Stats en 1 query con agregación condicional, scopeadas al staff de
        // este distribuidor. `available` existe tanto en distributor_staff
        // (pivot) como en users: sin calificar la columna, es ambigua.
        // toBase()->reorder(): sin esto, BelongsToMany::get() agrega las
        // columnas pivot_* al SELECT y MySQL rechaza mezclar columnas
        // agregadas con columnas sueltas sin GROUP BY (error 1140).
        $agg = $distributor->staffs()->toBase()->reorder()->selectRaw(
            'COUNT(*) total,
             SUM(users.available = 1) active,
             SUM(users.available = 0) inactive'
        )->first();

        $stats = [
            'total' => (int) $agg->total,
            'active' => (int) $agg->active,
            'inactive' => (int) $agg->inactive,
        ];

        return view('supports.views.distributors.staffs.index')->with([
            'users' => $users,
            'distributor' => $distributor,
            'available' => $available,
            'searchKey' => $searchKey,
            'stats' => $stats,
        ]);
    }

    public function create($slack)
    {
        $distributor = Distributor::slack($slack);

        $availables = collect([
            ['id' => '1', 'label' => 'Activo'],
            ['id' => '0', 'label' => 'Inactivo'],
        ]);

        $availables = $availables->pluck('label', 'id');

        return view('supports.views.distributors.staffs.create')->with([
            'distributor' => $distributor,
            'availables' => $availables,
        ]);
    }

    public function edit($slack)
    {
        $user = $this->guardManageableUser(User::slack($slack));

        $distributor = $user->relationsDistributor;

        $availables = collect([
            ['id' => '1', 'label' => 'Activo'],
            ['id' => '0', 'label' => 'Inactivo'],
        ]);

        $availables = $availables->pluck('label', 'id');

        return view('supports.views.distributors.staffs.edit')->with([
            'user' => $user,
            'distributor' => $distributor,
            'availables' => $availables,
        ]);
    }

    public function view($slack)
    {
        $user = $this->guardManageableUser(User::slack($slack));

        $availables = collect([
            ['id' => '1', 'label' => 'Activo'],
            ['id' => '0', 'label' => 'Inactivo'],
        ]);

        $availables = $availables->pluck('label', 'id');

        return view('supports.views.distributors.staffs.view')->with([
            'user' => $user,
            'availables' => $availables,
        ]);
    }

    public function reports($slack)
    {
        $distributor = Distributor::slack($slack);

        return view('supports.views.distributors.staffs.reports')->with([
            'distributor' => $distributor,
        ]);
    }

    public function generate(Request $request)
    {
        $distributor = Distributor::slack($request->distributor);

        $range = parse_date_range($request->range);

        if ($range === null) {
            return back()->with('error', 'Selecciona un rango de fechas válido para generar el reporte.');
        }

        [$start, $end] = $range;

        return Excel::download(
            new StaffExport($distributor, $request->available, $start, $end),
            'Reporte Empleados.xlsx'
        );
    }

    public function update(Request $request)
    {
        // Ownership: bloquea editar/resetear password de un manager u otro
        // support (evita escalada de privilegios) — mismo guard que edit/view/destroy.
        $user = $this->guardManageableUser(User::slack($request->slack));

        if ($error = $this->uniqueUserFieldError($request->email, $request->identification, $user)) {
            return response()->json(['success' => false, 'message' => $error]);
        }

        $user->firstname = Str::upper($request->firstname);
        $user->lastname = Str::upper($request->lastname);
        $user->cellphone = $request->cellphone;
        $user->email = $request->email;
        $user->address = $request->address;
        $user->company = $request->company;
        $user->available = $request->available;
        $user->identification = $request->identification;

        if ($request->filled('password')) {
            $user->password = $request->password;
        }

        $user->save();

        return response()->json(['success' => true, 'message' => 'Se ha actualizado correctamente el perfil']);
    }

    public function store(Request $request)
    {
        $distributor = Distributor::slack($request->distributor);

        if ($error = $this->uniqueUserFieldError($request->email, $request->identification)) {
            return response()->json(['success' => false, 'message' => $error]);
        }

        DB::transaction(function () use ($request, $distributor) {
            $user = new User;
            $user->slack = $this->generate_slack('users');
            $user->firstname = Str::upper($request->firstname);
            $user->lastname = Str::upper($request->lastname);
            $user->cellphone = $request->cellphone;
            $user->identification = $request->identification;
            $user->email = $request->email;
            $user->address = $request->address;
            $user->password = $request->password;
            $user->available = 1;
            $user->role = 'distributor';
            $user->terms = 1;
            $user->page = 0;
            $user->setting = 0;
            $user->validation = 1;
            $user->email_verified_at = Carbon::now()->setTimezone('America/Bogota');
            $user->save();

            $inscription = new DistributorStaff;
            $inscription->user_id = $user->id;
            $inscription->distributor_id = $distributor->id;
            $inscription->available = 1;
            $inscription->created_at = Carbon::now()->setTimezone('America/Bogota');
            $inscription->updated_at = Carbon::now()->setTimezone('America/Bogota');
            $inscription->save();
        });

        return response()->json(['success' => true, 'message' => 'El empleado se creo correctamente.']);
    }

    public function destroy($slack)
    {
        $user = $this->guardManageableUser(User::slack($slack));
        $user->delete();

        return redirect()->back();
    }

    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'action' => ['required', 'in:delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:users,id'],
        ]);

        // Mismo guard que destroy(): un soporte no puede eliminar en lote
        // usuarios con roles no gestionables (manager/support).
        $query = User::whereIn('id', $request->ids)->whereIn('role', $this->manageableRoles);
        $count = $query->count();

        match ($request->action) {
            'delete' => $query->delete(),
        };

        return response()->json(['success' => true, 'message' => $count.' empleado(s) procesados.']);
    }
}
