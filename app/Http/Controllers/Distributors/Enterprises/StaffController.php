<?php

namespace App\Http\Controllers\Distributors\Enterprises;

use App\Http\Controllers\Controller;
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
    public function index(Request $request, $slack)
    {

        $enterprise = Enterprise::slack($slack);
        $searchKey = $request->search;
        $available = $request->available;

        $users = $enterprise->staffs()->descending();

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

        return view('distributors.views.enterprises.staffs.index')->with([
            'users' => $users,
            'enterprise' => $enterprise,
            'available' => $available,
            'searchKey' => $searchKey,
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

        return view('distributors.views.enterprises.staffs.create')->with([
            'enterprise' => $enterprise,
            'availables' => $availables,
        ]);
    }

    public function edit($slack)
    {

        $user = User::slack($slack);

        $enterprise = $user->relationsenterprise;

        $availables = collect([
            ['id' => '1', 'label' => 'Activo'],
            ['id' => '0', 'label' => 'Inactivo'],
        ]);

        $availables = $availables->pluck('label', 'id');

        return view('distributors.views.enterprises.staffs.edit')->with([
            'user' => $user,
            'enterprise' => $enterprise,
            'availables' => $availables,
        ]);

    }

    public function update(Request $request): JsonResponse
    {
        $user = User::slack($request->slack);

        if (User::where('email', $request->email)->where('id', '!=', $user->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'El correo electrónico ya está registrado en nuestro sistema.']);
        }

        if (User::where('identification', $request->identification)->where('id', '!=', $user->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'La identificación ya está registrada en nuestro sistema.']);
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

        $user->update();

        return response()->json(['success' => true, 'message' => 'Se ha actualizado correctamente el empleado.']);
    }

    public function store(Request $request): JsonResponse
    {
        $enterprise = Enterprise::slack($request->enterprise);

        if (User::where('email', $request->email)->exists()) {
            return response()->json(['success' => false, 'message' => 'El correo electrónico ya está registrado en nuestro sistema.']);
        }

        if (User::where('identification', $request->identification)->exists()) {
            return response()->json(['success' => false, 'message' => 'La identificación ya está registrada en nuestro sistema.']);
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
            $user->available = 1;
            $user->role = 'enterprise';
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

        return response()->json(['success' => true, 'message' => 'Se ha creado correctamente el empleado.']);
    }

    public function destroy($slack)
    {

        $user = User::slack($slack);
        $user->delete();

        return redirect()->back();

    }
}
