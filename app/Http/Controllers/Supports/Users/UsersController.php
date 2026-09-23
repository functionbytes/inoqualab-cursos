<?php

namespace App\Http\Controllers\Supports\Users;

use App\Events\Auth\Password\ResetPasswordCreated;
use App\Http\Controllers\Concerns\RestrictsManageableUsers;
use App\Http\Controllers\Controller;
use App\Http\Requests\Supports\Users\BulkActionUserRequest;
use App\Http\Requests\Supports\Users\StoreUserRequest;
use App\Models\Enterprise\Enterprise;
use App\Models\Enterprise\EnterpriseUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UsersController extends Controller
{
    use RestrictsManageableUsers;

    public function index(Request $request)
    {

        $searchKey = $request->search;
        $role = $request->role;

        $users = User::descending();

        if ($searchKey) {
            $users = $users->where(function ($query) use ($searchKey) {
                $query->where('users.firstname', 'like', '%'.$searchKey.'%')
                    ->orWhere('users.lastname', 'like', '%'.$searchKey.'%')
                    ->orWhere(DB::raw("CONCAT(users.firstname, ' ', users.lastname)"), 'like', '%'.$searchKey.'%')
                    ->orWhere('users.email', 'like', '%'.$searchKey.'%')->orWhere('users.identification', 'like', '%'.$searchKey.'%');
            });
        }

        if ($request->role != null) {
            $users = $users->where('role', $role);
        }

        $users = $users->paginate(paginationNumber());

        // Stats con una sola query de agregación (en vez de 4 counts separados).
        $agg = User::query()->selectRaw(
            'COUNT(*) total,
             SUM(role = "customer") customers,
             SUM(role = "enterprise") enterprises,
             SUM(available = 1) actives'
        )->first();

        $stats = [
            'total' => (int) $agg->total,
            'customers' => (int) $agg->customers,
            'enterprises' => (int) $agg->enterprises,
            'actives' => (int) $agg->actives,
        ];

        $view = $request->ajax() ? 'supports.views.users.users._table' : 'supports.views.users.users.index';

        return view($view)->with([
            'users' => $users,
            'role' => $role,
            'searchKey' => $searchKey,
            'stats' => $stats,
        ]);
    }

    public function create()
    {

        $roles = collect([
            ['id' => 'customer', 'title' => 'Cliente'],
            ['id' => 'enterprise', 'title' => 'Empresa'],
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

        return view('supports.views.users.users.create')->with([
            'roles' => $roles,
            'enterprises' => $enterprises,
            'availables' => $availables,
        ]);

    }

    public function store(StoreUserRequest $request)
    {
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
        $user->available = 1;
        $user->password = $request->password;
        $user->terms = 1;
        $user->page = 1;
        $user->setting = 1;
        $user->validation = 1;
        $user->email_verified_at = Carbon::now()->setTimezone('America/Bogota');
        // El <select id="enterprises"> del formulario envía la clave 'enterprises'
        // (plural), no 'enterprise': leerla en singular dejaba enterprise_id
        // siempre null para cualquier usuario staff creado con rol 'enterprise'.
        $user->enterprise_id = $request->role === 'enterprise' ? $request->enterprises : null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Se ha creado correctamente.',
        ]);

    }

    public function view($slack)
    {

        $user = $this->guardManageableUser(User::slack($slack));

        $roles = collect([
            ['id' => 'customers', 'title' => 'Cliente'],
            ['id' => 'enterprises', 'title' => 'Empresa'],
        ]);

        $roles = $roles->pluck('title', 'id');

        $availables = collect([
            ['id' => '1', 'label' => 'Publico'],
            ['id' => '0', 'label' => 'Oculto'],
        ]);

        $availables = $availables->pluck('label', 'id');

        return view('supports.views.users.users.view')->with([
            'user' => $user,
            'roles' => $roles,
            'availables' => $availables,
        ]);

    }

    public function navegation($slack)
    {
        $user = $this->guardManageableUser(User::slack($slack));

        $counts = [
            'orders' => $user->orders()->count(),
            'inscriptions' => $user->inscriptions()->count(),
            'certificates' => $user->certificates()->count(),
            'results' => $user->certificates()->count(),
        ];

        return view('supports.views.users.users.navegation')->with([
            'user' => $user,
            'counts' => $counts,
        ]);

    }

    public function edit($slack)
    {
        $user = $this->guardManageableUser(User::slack($slack));

        $roles = collect([
            ['id' => 'customer', 'title' => 'Cliente'],
            ['id' => 'enterprise', 'title' => 'Empresa'],
            ['id' => 'distributor', 'title' => 'Distribuidor'],
            ['id' => 'accounting', 'title' => 'Contabilidad'],
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

        return view('supports.views.users.users.edit')->with([
            'user' => $user,
            'enterprises' => $enterprises,
            'enterprise' => $enterprise,
            'availables' => $availables,
            'roles' => $roles,
        ]);

    }

    public function update(Request $request)
    {
        $user = User::slack($request->slack);

        // El usuario objetivo debe existir y tener un rol gestionable por soporte
        // (no puede editar managers/otros supports — evita escalada de privilegios).
        if (! $user || ! in_array($user->role, $this->manageableRoles, true)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes autorización para gestionar este usuario.',
            ], 403);
        }

        // El nuevo rol asignado también debe estar dentro de los gestionables.
        if (! in_array($request->role, $this->manageableRoles, true)) {
            return response()->json([
                'success' => false,
                'message' => 'El rol seleccionado no está permitido.',
            ], 422);
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
            // Mismo bug que en store(): el <select id="enterprises"> envía
            // 'enterprises' (plural); leer 'enterprise' (singular) borraba
            // enterprise_id en cada guardado de un usuario staff.
            $user->enterprise_id = $request->enterprises;
        } elseif ($request->role == 'customer') {
            // Un cliente sin empresa asignada (la mayoría) deja el combo
            // "Empresa" vacío: crear/actualizar el EnterpriseUser en ese caso
            // intentaba grabar enterprise_id NULL, y esa columna es NOT NULL
            // -> 500 al guardar CUALQUIER cambio de un cliente sin empresa.
            $relation = $user->relation;
            if ($request->enterprises) {
                if ($relation) {
                    $relation->enterprise_id = $request->enterprises;
                    $relation->save();
                } else {
                    EnterpriseUser::create([
                        'user_id' => $user->id,
                        'enterprise_id' => $request->enterprises,
                        'available' => 1,
                        'created_at' => Carbon::now()->setTimezone('America/Bogota'),
                        'updated_at' => Carbon::now()->setTimezone('America/Bogota'),
                    ]);
                }
            } elseif ($relation) {
                $relation->delete();
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
        $user = User::slack($slack);

        // Solo permite eliminar usuarios con rol gestionable (no managers/supports).
        if (! $user || ! in_array($user->role, $this->manageableRoles, true)) {
            return redirect()->back();
        }

        $user->delete();

        return redirect()->back();
    }

    public function bulkAction(BulkActionUserRequest $request): JsonResponse
    {
        // Mismo guard que destroy(): solo permite eliminar en lote usuarios
        // con rol gestionable (no managers/supports).
        $query = User::whereIn('id', $request->ids)->whereIn('role', $this->manageableRoles);
        $count = $query->count();

        match ($request->action) {
            'delete' => $query->delete(),
        };

        return response()->json(['success' => true, 'message' => $count.' usuario(s) procesados.']);
    }

    public function information(Request $request)
    {
        // Solo permite asignar roles gestionables (evita escalada de privilegios).
        $this->assertManageableRole($request->role);

        $user = User::slack($request->slack);
        abort_unless($user instanceof User, 404);
        abort_unless(in_array($user->role, $this->manageableRoles, true), 403);

        if (User::where('email', $request->email)->where('id', '!=', $user->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'El correo electrónico ya está registrado en nuestro sistema.']);
        }

        if ($request->identification && User::where('identification', $request->identification)->where('id', '!=', $user->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'La identificación ya está registrada en nuestro sistema.']);
        }

        $user->firstname = Str::upper($request->firstname);
        $user->lastname = Str::upper($request->lastname);
        $user->cellphone = $request->cellphone;
        $user->email = $request->email;
        $user->address = $request->address;
        $user->role = $request->role;
        $user->available = $request->available;

        if ($user->isDirty()) {
            $user->save();
            activity()->performedOn($user)->withProperties($user->getChanges())->log('updated');
        }

        return response()->json(['success' => true, 'message' => 'Usuario actualizado correctamente.']);
    }

    public function resetpassword(Request $request)
    {
        if ($request->new_password !== $request->new_password_confirmation) {
            return response()->json([
                'success' => false,
                'message' => 'Las contraseñas no coinciden.',
            ], 422);
        }

        // Escalada: un soporte NO puede resetear la contraseña de un manager/support.
        $user = $this->guardManageableUser(User::slack($request->slack));

        $user->password = $request->new_password;
        $user->remember_token = Str::random(60);
        $user->password_reset_token = null;
        $user->password_reset_max_tries = null;
        $user->password_reset_last_tried_on = null;

        // Soporte cambia la contraseña de un tercero: su sesión abierta debe caer.
        revokeUserSessions($user);

        event(new ResetPasswordCreated($user));

        $user->save();

        activity()
            ->performedOn($user)
            ->withProperties($user->getChanges())
            ->log('updated');

        return response()->json([
            'success' => true,
            'message' => 'Contraseña actualizada correctamente.',
        ]);
    }

    public function notification(Request $request)
    {

        // Único mutador del controller que no pasaba por este guard: un
        // support podía alterar las preferencias de notificación de un
        // manager/support sin ninguna verificación de rol, a diferencia de
        // update/information/resetpassword/destroy que sí lo exigen.
        $user = $this->guardManageableUser(User::slack($request->slack));

        $user->newsletter_notification = $request->newsletter_notification == 'true' ? 1 : 0;
        $user->order_notification = $request->order_notification == 'true' ? 1 : 0;
        $user->status_notification = $request->status_notification == 'true' ? 1 : 0;
        $user->email_notification = $request->email_notification == 'true' ? 1 : 0;
        $user->cookies_notification = $request->cookies_notification == 'true' ? 1 : 0;

        if ($user->isDirty()) {

            $user->update();

            activity()
                ->performedOn($user)
                ->withProperties($user->getChanges())
                ->log('updated');
        }

        return response()->json([
            'success' => true,
            'message' => 'Se ha actualizado correctamente.',
        ]);

    }
}
