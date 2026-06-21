<?php

namespace App\Http\Controllers\Managers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Users\StoreUserRequest;
use App\Models\Enterprise\Enterprise;
use App\Models\Enterprise\EnterpriseUser;
use App\Models\Order\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UsersController extends Controller
{
    public function index(Request $request)
    {

        $searchKey = $request->search;
        $role = $request->role;

        $users = User::descending();

        if ($searchKey) {
            $users->when(! strpos($searchKey, '-'), function ($query) use ($searchKey) {
                $query->where('users.firstname', 'like', '%'.$searchKey.'%')
                    ->orWhere('users.lastname', 'like', '%'.$searchKey.'%')
                    ->orWhere(DB::raw("CONCAT(users.firstname, ' ', users.lastname)"), 'like', '%'.$searchKey.'%')
                    ->orWhere('users.email', 'like', '%'.$searchKey.'%')
                    ->orWhere('users.identification', 'like', '%'.$searchKey.'%');
            });
        }

        if ($request->role != null) {
            $users = $users->where('role', $role);
        }

        $users = $users->paginate(paginationNumber());

        return view('managers.views.users.users.index')->with([
            'users' => $users,
            'role' => $role,
            'searchKey' => $searchKey,
        ]);
    }

    public function create()
    {
        $roles = collect([
            ['id' => 'manager', 'title' => 'Administrador'],
            ['id' => 'customer', 'title' => 'Cliente'],
            ['id' => 'enterprise', 'title' => 'Empresa'],
            ['id' => 'distributor', 'title' => 'Distribuidor'],
            ['id' => 'accounting', 'title' => 'Contabilidad'],
            ['id' => 'support', 'title' => 'Suporte'],
        ]);

        $roles = $roles->pluck('title', 'id');

        $enterprises = Enterprise::get();
        $enterprises->prepend('', '');
        $enterprises = $enterprises->pluck('title', 'id');

        $availables = collect([
            ['id' => '1', 'label' => 'Activo'],
            ['id' => '0', 'label' => 'Inactivo'],
        ]);

        $availables = $availables->pluck('label', 'id');

        return view('managers.views.users.users.create')->with([
            'roles' => $roles,
            'enterprises' => $enterprises,
            'availables' => $availables,
        ]);

    }

    public function store(StoreUserRequest $request)
    {
        abort_unless(auth()->user()->can('users.create'), 403);
        $user = new User;
        $user->slack = $this->generate_slack('users');
        $user->firstname = Str::upper($request->firstname);
        $user->lastname = Str::upper($request->lastname);
        $user->cellphone = $request->cellphone;
        $user->identification = $request->identification;
        $user->email = $request->email;
        $user->address = $request->address;
        $user->company = $request->company;
        $user->role = $request->role;
        $user->password = $request->password;
        $user->available = 1;
        $user->terms = 1;
        $user->page = 1;
        $user->setting = 1;
        $user->validation = 1;
        $user->email_verified_at = Carbon::now()->setTimezone('America/Bogota');
        $user->enterprise_id = $request->role === 'enterprise' ? $request->enterprise : null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Se ha creado correctamente',
        ]);

    }

    public function view($slack)
    {
        $user = User::slack($slack);

        $roles = collect([
            ['id' => 'manager', 'title' => 'Administrador'],
            ['id' => 'customer', 'title' => 'Cliente'],
            ['id' => 'enterprise', 'title' => 'Empresa'],
            ['id' => 'distributor', 'title' => 'Distribuidor'],
            ['id' => 'accounting', 'title' => 'Contabilidad'],
            ['id' => 'support', 'title' => 'Suporte'],
        ]);

        $roles = $roles->pluck('title', 'id');

        $availables = $this->availableOptions();

        return view('managers.views.users.users.view')->with([
            'user' => $user,
            'roles' => $roles,
            'availables' => $availables,
        ]);

    }

    public function edit($slack)
    {
        $user = User::slack($slack);

        $roles = collect([
            ['id' => 'manager', 'title' => 'Administrador'],
            ['id' => 'customer', 'title' => 'Cliente'],
            ['id' => 'enterprise', 'title' => 'Empresa'],
            ['id' => 'distributor', 'title' => 'Distribuidor'],
            ['id' => 'accounting', 'title' => 'Contabilidad'],
            ['id' => 'support', 'title' => 'Suporte'],
        ]);

        $roles = $roles->pluck('title', 'id');

        $availables = collect([
            ['id' => '1', 'label' => 'Activo'],
            ['id' => '0', 'label' => 'Inactivo'],
        ]);

        $availables = $availables->pluck('label', 'id');

        $enterprises = Enterprise::get();
        $enterprises->prepend('', '');
        $enterprises = $enterprises->pluck('title', 'id');

        $enterprise = $user->relations?->id;

        return view('managers.views.users.users.edit')->with([
            'user' => $user,
            'enterprises' => $enterprises,
            'enterprise' => $enterprise,
            'availables' => $availables,
            'roles' => $roles,
        ]);

    }

    public function update(Request $request)
    {
        abort_unless(auth()->user()->can('users.update'), 403);
        $user = User::slack($request->slack);

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Usuario no encontrado.']);
        }

        // El rol debe pertenecer a la lista permitida (evita roles inválidos o escalada).
        $validRoles = ['manager', 'customer', 'enterprise', 'distributor', 'accounting', 'support'];
        if (! in_array($request->role, $validRoles, true)) {
            return response()->json(['success' => false, 'message' => 'El rol seleccionado no es válido.']);
        }

        // Validación de cambios en email o identificación
        if ($user->email != $request->email || $user->identification != $request->identification) {
            $emailExists = User::where('email', $request->email)->where('id', '!=', $user->id)->exists();
            if ($emailExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'El correo electrónico ya está registrado en nuestro sistema',
                ]);
            }

            $identificationExists = User::where('identification', $request->identification)->where('id', '!=', $user->id)->exists();
            if ($identificationExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'El NIT ya está registrado en nuestro sistema',
                ]);
            }
        }

        // Actualización de datos del usuario
        $user->firstname = Str::upper($request->firstname);
        $user->lastname = Str::upper($request->lastname);
        $user->cellphone = $request->cellphone;
        $user->email = $request->email;
        $user->address = $request->address;
        $user->company = $request->company;
        $user->role = $request->role;
        $user->available = $request->available;
        if ($request->password) {
            $user->password = $request->password;
        }

        if ($request->role == 'enterprise') {
            $user->enterprise_id = $request->enterprise;
        } elseif ($request->role == 'customer') {
            $enterprise = $user->relation;
            if ($enterprise) {
                $enterprise->enterprise_id = $request->enterprises;
                $enterprise->save();
            } else {
                EnterpriseUser::create([
                    'user_id' => $user->id,
                    'enterprise_id' => $request->enterprises,
                    'available' => 1,
                    'created_at' => Carbon::now()->setTimezone('America/Bogota'),
                    'updated_at' => Carbon::now()->setTimezone('America/Bogota'),
                ]);
            }
        } else {
            $user->enterprise_id = null;
            $relation = $user->relation;
            if ($relation) {
                $relation->delete();
            }
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Se ha actualizado correctamente',
        ]);
    }

    public function destroy($slack)
    {
        abort_unless(auth()->user()->can('users.delete'), 403);
        $user = User::slack($slack);
        $user->delete();

        return redirect()->route('manager.users');

    }

    public function orders(Request $request, $slack)
    {

        $user = User::slack($slack);

        $orders = $user->orders;

        return view('managers.views.users.users.orders')->with([
            'orders' => $orders,
        ]);

    }

    public function destroyOrders($slack)
    {
        $user = null;
        $order = Order::slack($slack);
        $user = $order->user->slack;
        $order->delete();

        return redirect()->route('manager.users.orders', $user);

    }
}
