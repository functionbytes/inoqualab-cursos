<?php

namespace App\Http\Controllers\Managers\Distributors;

use App\Http\Controllers\Controller;
use App\Models\Distributor\Distributor;
use App\Models\Distributor\DistributorStaff;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StaffController extends Controller
{
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

        return view('managers.views.distributors.staffs.index')->with([
            'users' => $users,
            'distributor' => $distributor,
            'available' => $available,
            'searchKey' => $searchKey,
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

        return view('managers.views.distributors.staffs.create')->with([
            'distributor' => $distributor,
            'availables' => $availables,
        ]);

    }

    public function edit($slack)
    {

        $user = User::slack($slack);

        $distributor = $user->relationsDistributor;

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

        $user = User::slack($slack);

        $availables = collect([
            ['id' => '1', 'label' => 'Activo'],
            ['id' => '0', 'label' => 'Inactivo'],
        ]);

        $availables = $availables->pluck('label', 'id');

        return view('managers.views.distributors.staffs.view')->with([
            'user' => $user,
            'availables' => $availables,
        ]);
    }

    public function history($slack)
    {
        $user = User::slack($slack);

        return view('managers.views.distributors.staffs.history')->with([
            'user' => $user,
        ]);

    }

    public function update(Request $request)
    {
        $user = User::slack($request->slack);

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Usuario no encontrado.']);
        }

        if ($user->email !== $request->email) {
            $emailExists = User::where('email', $request->email)->where('id', '!=', $user->id)->exists();
            if ($emailExists) {
                return response()->json(['success' => false, 'message' => 'El correo electronico ya estan regitrada en nuestro sistema']);
            }
        }

        if ($request->identification && $user->identification !== $request->identification) {
            $identificationExists = User::where('identification', $request->identification)->where('id', '!=', $user->id)->exists();
            if ($identificationExists) {
                return response()->json(['success' => false, 'message' => 'El nit ya estan regitrada en nuestro sistema']);
            }
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

        return response()->json([
            'success' => true,
            'message' => 'El empleado se actaulizo correctamente.',
        ]);
    }

    public function store(Request $request)
    {
        $distributor = Distributor::slack($request->distributor);

        $emailExists = User::where('email', $request->email)->exists();
        if ($emailExists) {
            return response()->json(['success' => false, 'message' => 'El correo electronico ya estan regitrada en nuestro sistema']);
        }

        if ($request->identification) {
            $identificationExists = User::where('identification', $request->identification)->exists();
            if ($identificationExists) {
                return response()->json(['success' => false, 'message' => 'El nit ya estan regitrada en nuestro sistema']);
            }
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

        return response()->json([
            'success' => true,
            'message' => 'El empleado se creo correctamente.',
        ]);
    }

    public function destroy($slack)
    {

        $user = User::slack($slack);
        $user->delete();

        return redirect()->back();

    }
}
