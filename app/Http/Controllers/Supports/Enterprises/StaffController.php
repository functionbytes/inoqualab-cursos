<?php

namespace App\Http\Controllers\Supports\Enterprises;

use App\Http\Controllers\Concerns\RestrictsManageableUsers;
use App\Http\Controllers\Concerns\ValidatesUserPassword;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Supports\Concerns\ValidatesUniqueUserFields;
use App\Http\Requests\Supports\BulkActionEnterpriseStaffRequest;
use App\Models\Enterprise\Enterprise;
use App\Models\Enterprise\EnterpriseStaff;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StaffController extends Controller
{
    use RestrictsManageableUsers, ValidatesUniqueUserFields, ValidatesUserPassword;

    public function index(Request $request, $slack)
    {
        $enterprise = Enterprise::slack($slack);
        $searchKey = $request->search;
        $available = $request->available;

        $users = $enterprise->staffs()->orderBy('updated_at', 'desc');

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

        // 1 query de agregación en vez de 3 counts sueltos.
        $agg = DB::table('enterprise_staff')
            ->join('users', 'users.id', '=', 'enterprise_staff.user_id')
            ->where('enterprise_staff.enterprise_id', $enterprise->id)
            ->selectRaw(
                'COUNT(*) total,
                 SUM(users.available = 1) active,
                 SUM(users.available = 0) inactive'
            )->first();

        $stats = [
            'total' => (int) $agg->total,
            'active' => (int) $agg->active,
            'inactive' => (int) $agg->inactive,
        ];

        $view = $request->ajax() ? 'supports.views.enterprises.staffs._table' : 'supports.views.enterprises.staffs.index';

        return view($view)->with([
            'users' => $users,
            'enterprise' => $enterprise,
            'available' => $available,
            'searchKey' => $searchKey,
            'stats' => $stats,
        ]);
    }

    public function create($slack)
    {
        $enterprise = Enterprise::slack($slack);

        $availables = collect([
            ['id' => '1', 'label' => 'Activo'],
            ['id' => '0', 'label' => 'Inactivo'],
        ]);

        $availables = $availables->pluck('label', 'id');

        return view('supports.views.enterprises.staffs.create')->with([
            'enterprise' => $enterprise,
            'availables' => $availables,
        ]);
    }

    public function edit($slack)
    {
        $user = $this->guardManageableUser(User::slack($slack));

        // $enterprise->distributor sobre null reventaba antes de llegar a la
        // vista: sin empresa asociada no hay ficha de staff que editar.
        $enterprise = $user->relationsEnterprises;
        abort_if($enterprise === null, 404);

        $distributor = $enterprise->distributor;

        $availables = collect([
            ['id' => '1', 'label' => 'Activo'],
            ['id' => '0', 'label' => 'Inactivo'],
        ]);

        $availables = $availables->pluck('label', 'id');

        return view('supports.views.enterprises.staffs.edit')->with([
            'user' => $user,
            'enterprise' => $enterprise,
            'availables' => $availables,
            'distributor' => $distributor,
        ]);
    }

    public function update(Request $request)
    {
        // Escalada: un soporte NO puede editar un manager/support (solo roles gestionables).
        $user = $this->guardManageableUser(User::slack($request->slack));

        if ($error = $this->uniqueUserFieldError($request->email, $request->identification, $user)) {
            return response()->json(['success' => false, 'message' => $error]);
        }

        if ($error = $this->passwordValidationError($request->password)) {
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

        return response()->json(['success' => true, 'message' => 'Se ha actualizado correctamente el empleado']);
    }

    public function store(Request $request)
    {
        $enterprise = Enterprise::slack($request->enterprise);

        if ($error = $this->uniqueUserFieldError($request->email, $request->identification)) {
            return response()->json(['success' => false, 'message' => $error]);
        }

        DB::transaction(function () use ($request, $enterprise) {
            $user = new User;
            $user->slack = $this->generate_slack('users');
            $user->firstname = Str::upper($request->firstname);
            $user->lastname = Str::upper($request->lastname);
            $user->cellphone = $request->cellphone;
            $user->identification = $request->identification;
            $user->email = $request->email;
            $user->password = $request->password;
            $user->role = 'enterprise';
            $user->available = 1;
            $user->terms = 1;
            $user->page = 0;
            $user->setting = 0;
            $user->validation = 1;
            $user->email_verified_at = Carbon::now()->setTimezone('America/Bogota');
            $user->save();

            $inscription = new EnterpriseStaff;
            $inscription->user_id = $user->id;
            $inscription->enterprise_id = $enterprise->id;
            $inscription->available = 1;
            $inscription->created_at = Carbon::now()->setTimezone('America/Bogota');
            $inscription->updated_at = Carbon::now()->setTimezone('America/Bogota');
            $inscription->save();
        });

        return response()->json(['success' => true, 'message' => 'Se ha creado correctamente el empleado']);
    }

    public function destroy($slack)
    {
        $user = $this->guardManageableUser(User::slack($slack));
        $user->delete();

        return redirect()->back();
    }

    public function bulkAction(BulkActionEnterpriseStaffRequest $request): JsonResponse
    {
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
