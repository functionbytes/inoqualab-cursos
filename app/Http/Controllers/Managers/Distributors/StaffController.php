<?php

namespace App\Http\Controllers\Managers\Distributors;

use App\Http\Controllers\Concerns\RestrictsManageableUsers;
use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Distributors\BulkActionDistributorStaffRequest;
use App\Http\Requests\Managers\Distributors\StoreDistributorStaffRequest;
use App\Http\Requests\Managers\Distributors\UpdateDistributorStaffRequest;
use App\Models\Distributor\Distributor;
use App\Models\Distributor\DistributorStaff;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StaffController extends Controller
{
    use RestrictsManageableUsers;

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

        $view = request()->ajax() ? 'managers.views.distributors.staffs._table' : 'managers.views.distributors.staffs.index';

        return view($view)->with([
            'users' => $users,
            'distributor' => $distributor,
            'available' => $available,
            'searchKey' => $searchKey,
        ]);

    }

    public function create($slack)
    {

        $distributor = Distributor::slack($slack);

        return view('managers.views.distributors.staffs.create')->with([
            'distributor' => $distributor,
        ]);

    }

    public function edit($slack)
    {

        $user = $this->guardManageableUser(User::slack($slack));

        // La vista pinta $distributor->slack: sin distribuidor asociado esta
        // ficha de staff no existe realmente y daba 500.
        $distributor = $user->relationsDistributor;
        abort_if($distributor === null, 404);

        $availables = collect([
            ['id' => '1', 'label' => 'Activo'],
            ['id' => '0', 'label' => 'Inactivo'],
        ]);

        $availables = $availables->pluck('label', 'id');

        return view('managers.views.distributors.staffs.edit')->with([
            'user' => $user,
            'distributor' => $distributor,
            'availables' => $availables,
        ]);
    }

    public function view($slack)
    {

        $user = $this->guardManageableUser(User::slack($slack));

        return view('managers.views.distributors.staffs.view')->with([
            'user' => $user,
        ]);
    }

    public function update(UpdateDistributorStaffRequest $request)
    {
        abort_unless(auth()->user()->can('distributors.update'), 403);

        $data = $request->validated();
        $user = $this->guardManageableUser(User::slack($data['slack']));

        if ($user->email !== $data['email']) {
            $emailExists = User::where('email', $data['email'])->where('id', '!=', $user->id)->exists();
            if ($emailExists) {
                return response()->json(['success' => false, 'message' => 'El correo electronico ya estan regitrada en nuestro sistema']);
            }
        }

        if (! empty($data['identification']) && $user->identification !== $data['identification']) {
            $identificationExists = User::where('identification', $data['identification'])->where('id', '!=', $user->id)->exists();
            if ($identificationExists) {
                return response()->json(['success' => false, 'message' => 'El nit ya estan regitrada en nuestro sistema']);
            }
        }

        $user->firstname = Str::upper($data['firstname']);
        $user->lastname = Str::upper($data['lastname']);
        $user->cellphone = $data['cellphone'] ?? null;
        $user->email = $data['email'];
        $user->address = $data['address'] ?? null;
        $user->available = $data['available'];
        $user->identification = $data['identification'] ?? null;

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->update();

        return response()->json([
            'success' => true,
            'message' => 'El empleado se actaulizo correctamente.',
        ]);
    }

    public function store(StoreDistributorStaffRequest $request)
    {
        abort_unless(auth()->user()->can('distributors.create'), 403);

        $data = $request->validated();
        $distributor = Distributor::slack($data['distributor']);

        $emailExists = User::where('email', $data['email'])->exists();
        if ($emailExists) {
            return response()->json(['success' => false, 'message' => 'El correo electronico ya estan regitrada en nuestro sistema']);
        }

        if (! empty($data['identification'])) {
            $identificationExists = User::where('identification', $data['identification'])->exists();
            if ($identificationExists) {
                return response()->json(['success' => false, 'message' => 'El nit ya estan regitrada en nuestro sistema']);
            }
        }

        DB::transaction(function () use ($data, $distributor) {
            $user = new User;
            $user->slack = $this->generate_slack('users');
            $user->firstname = Str::upper($data['firstname']);
            $user->lastname = Str::upper($data['lastname']);
            $user->cellphone = $data['cellphone'] ?? null;
            $user->identification = $data['identification'] ?? null;
            $user->email = $data['email'];
            $user->address = $data['address'] ?? null;
            $user->password = $data['password'];
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

        return response()->json([
            'success' => true,
            'message' => 'El empleado se creo correctamente.',
        ]);
    }

    public function destroy($slack)
    {
        abort_unless(auth()->user()->can('distributors.delete'), 403);

        $user = $this->guardManageableUser(User::slack($slack));
        $user->delete();

        return redirect()->back();

    }

    public function bulkAction(BulkActionDistributorStaffRequest $request, $slack)
    {
        $distributor = Distributor::slack($slack);

        // Igual que guardManageableUser(): solo roles gestionables y solo
        // empleados que realmente pertenecen a este distribuidor (IDOR).
        $memberIds = $distributor->staffs()
            ->whereIn('users.id', $request->ids)
            ->whereIn('users.role', $this->manageableRoles)
            ->pluck('users.id');

        $query = User::whereIn('id', $memberIds);
        $count = $query->count();

        match ($request->action) {
            'activate' => $query->update(['available' => 1]),
            'deactivate' => $query->update(['available' => 0]),
            'delete' => $query->delete(),
        };

        return response()->json(['success' => true, 'message' => $count.' empleado(s) procesados.']);
    }
}
