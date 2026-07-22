<?php

namespace App\Http\Controllers\Managers\Users;

use App\Enums\OrderCondition as Condition;
use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Users\StoreUserRequest;
use App\Models\Enterprise\Enterprise;
use App\Models\Enterprise\EnterpriseUser;
use App\Models\Order\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UsersController extends Controller
{
    /** Roles asignables desde este panel (excluye 'superadmin', que no se toca desde aquí). */
    private const ASSIGNABLE_ROLES = ['manager', 'customer', 'enterprise', 'distributor', 'accounting', 'support'];

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
        $roles = $this->roleOptions();

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
        abort_unless(
            $this->actorMayAssignRole($request->role),
            403,
            'No tienes privilegios suficientes para asignar este rol.'
        );

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
            'slack' => $user->slack,
        ]);

    }

    /**
     * Un manager (rol distinto de superadmin) no puede ver, editar, resetear
     * password ni eliminar una cuenta superadmin: `users.update`/`users.delete`
     * no distinguen el rol del objetivo, así que sin este guard un manager
     * podría tomar el control de una cuenta superadmin.
     */
    private function guardNotSuperadmin(User $user): void
    {
        abort_if(
            $user->role === 'superadmin' && auth()->user()->role !== 'superadmin',
            403,
            'No tienes autorización para gestionar esta cuenta.'
        );
    }

    /**
     * Techo de rol: un actor no puede asignar un rol cuyo conjunto de permisos
     * no esté totalmente contenido en el suyo propio. Mismo concepto que
     * RolesController::permissionsFrom(), aplicado a nivel de rol completo en
     * vez de permiso individual — evita que un manager con permisos
     * restringidos (ej. sin roles.*) convierta a otro usuario en un rol más
     * privilegiado del que él mismo posee.
     */
    private function actorMayAssignRole(string $role): bool
    {
        $actor = auth()->user();

        if ($actor->role === 'superadmin') {
            return true;
        }

        $rolePermissions = Role::where('name', $role)->where('guard_name', 'web')->first()?->permissions
            ?? collect();

        return $rolePermissions->every(fn ($permission) => $actor->can($permission->name));
    }

    private function roleOptions(): Collection
    {
        return collect([
            ['id' => 'manager', 'title' => 'Administrador'],
            ['id' => 'customer', 'title' => 'Cliente'],
            ['id' => 'enterprise', 'title' => 'Empresa'],
            ['id' => 'distributor', 'title' => 'Distribuidor'],
            ['id' => 'accounting', 'title' => 'Contabilidad'],
            ['id' => 'support', 'title' => 'Suporte'],
        ])->pluck('title', 'id');
    }

    public function view($slack)
    {
        $user = User::slack($slack);
        $this->guardNotSuperadmin($user);

        $roles = $this->roleOptions();

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
        $this->guardNotSuperadmin($user);

        $roles = $this->roleOptions();

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

        $this->guardNotSuperadmin($user);

        // El rol debe pertenecer a la lista permitida (evita roles inválidos o escalada).
        if (! in_array($request->role, self::ASSIGNABLE_ROLES, true)) {
            return response()->json(['success' => false, 'message' => 'El rol seleccionado no es válido.']);
        }

        // Techo de rol: el actor no puede asignar un rol con más privilegios que el suyo propio.
        if (! $this->actorMayAssignRole($request->role)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes privilegios suficientes para asignar este rol.',
            ]);
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
        // Sin fallback, un `available` ausente en el payload pondría el campo en
        // NULL en vez de conservar el valor existente.
        $user->available = $request->filled('available') ? $request->available : $user->available;
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
        $this->guardNotSuperadmin($user);
        $user->delete();

        return redirect()->route('manager.users');

    }

    public function orders(Request $request, $slack)
    {

        $user = User::slack($slack);

        // La vista pagina ($orders->links()): debe ser un paginador, no la Collection de la relación.
        $orders = $user->orders()->latest()->paginate(paginationNumber());

        return view('managers.views.users.users.orders')->with([
            'orders' => $orders,
        ]);

    }

    public function destroyOrders($slack)
    {
        // La ruta se llama manager.enterprises.users.orders.destroy, por lo que
        // EnforcePanelPermission deriva 'enterprises.delete' — un guard explícito
        // de 'orders.delete' evita que un rol con enterprises.* borre órdenes.
        abort_unless(auth()->user()->can('orders.delete'), 403);

        $order = Order::slack($slack);

        // El scope slack() devuelve el Builder cuando no hay match.
        if (! $order instanceof Order) {
            return redirect()->route('manager.users');
        }

        $userSlack = $order->user?->slack;

        // FK en cascada (orders → inscriptions → certificates): no permitir borrar
        // una orden pagada, arrastraría matrículas y certificados del alumno.
        if ($order->condition_id === Condition::Pagada->value) {
            return redirect()
                ->route('manager.users.orders', $userSlack ?? '')
                ->with('error', 'No se puede eliminar una orden pagada: arrastraría en cascada las matrículas y certificados del alumno.');
        }

        $order->delete();

        return redirect()->route('manager.users.orders', $userSlack ?? '');

    }
}
