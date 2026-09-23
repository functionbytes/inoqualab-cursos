<?php

namespace App\Http\Controllers\Supports\Enterprises;

use App\Http\Controllers\Concerns\RestrictsManageableUsers;
use App\Http\Controllers\Controller;
use App\Models\Enterprise\Enterprise;
use App\Models\Enterprise\EnterpriseUser;
use App\Models\User;
use Illuminate\Http\Request;

class ReassignController extends Controller
{
    use RestrictsManageableUsers;

    public function all($slack)
    {
        $enterprise = Enterprise::slack($slack);
        // Soporte gestiona TODAS las empresas (no está atado a un distribuidor):
        // `app('support')` es el propio usuario autenticado, que no tiene relación
        // `enterprises()` — antes esto era siempre null y crasheaba en prepend().
        $enterprises = Enterprise::available()->get();
        $users = $enterprise->users()->available()->get();

        $enterprises = $enterprises->pluck('title', 'slack');
        $users = $users->pluck('identification', 'identification');

        // Verificar si la clave existe antes de eliminarla
        if ($enterprises->has($enterprise->slack)) {
            $enterprises = $enterprises->forget($enterprise->slack);
        }

        $enterprises->prepend('', '');

        return view('supports.views.enterprises.reassigns.all')->with([
            'users' => $users,
            'enterprises' => $enterprises,
            'enterprise' => $enterprise,
        ]);
    }

    public function reassignAll(Request $request)
    {

        $identifications = explode(',', $request->users);
        // Enterprise::slack() aborta con 404 si no hay match (igual que
        // User::identification() en el comentario de abajo) -- para acá
        // abajo $newEnterprise nunca es null.
        $newEnterprise = Enterprise::slack($request->enterprise);

        // Carga en lote (2 queries fijas en vez de 2 por identificación --
        // antes N identificaciones disparaban 2N queries, un User::where()
        // y un EnterpriseUser::where() por cada una).
        $usersByIdentification = User::whereIn('identification', $identifications)->get()->keyBy('identification');
        $enterpriseUsersByUserId = EnterpriseUser::whereIn('user_id', $usersByIdentification->pluck('id'))->get()->keyBy('user_id');

        $enterpriseUserIdsToReassign = [];

        foreach ($identifications as $identification) {

            // User::identification() aborta con 404 si no hay match -- eso hacía
            // que el chequeo "!$user" de abajo fuera código muerto inalcanzable:
            // una identificación con typo abortaba el request COMPLETO con un
            // 404 crudo en vez de devolver el JSON de error ya escrito para
            // este caso (con el mensaje que sí identifica cuál falló).
            $user = $usersByIdentification->get($identification);

            if (! $user) {

                return response()->json([
                    'success' => false,
                    'message' => 'Usuario o empresa no encontrados.',
                ]);

            }

            // Mismo patrón que el resto del guard: JSON de error en vez de
            // abortar con 403 crudo, para no cortar el request en medio del
            // lote sin decir cuál identificación falló.
            if (! in_array($user->role, $this->manageableRoles, true)) {

                return response()->json([
                    'success' => false,
                    'message' => 'No tienes autorización para reasignar al usuario '.$identification.'.',
                ]);

            }

            $enterpriseUser = $enterpriseUsersByUserId->get($user->id);

            // Sin esta guarda, un usuario sin fila enterprise_user (2,737 casos
            // reales) crashea con "Attempt to assign property on null".
            if (! $enterpriseUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario '.$identification.' no tiene una empresa asignada para reasignar.',
                ]);
            }

            $enterpriseUserIdsToReassign[] = $enterpriseUser->id;

        }

        // Todos validados antes de escribir nada, y en 1 sola query: si algún
        // usuario de la lista fallaba a mitad del loop original, los
        // anteriores ya habían quedado reasignados sin forma de revertirlos.
        EnterpriseUser::whereIn('id', $enterpriseUserIdsToReassign)->update(['enterprise_id' => $newEnterprise->id]);

        return response()->json([
            'success' => true,
            'enterprise' => $newEnterprise->slack,
            'message' => 'Usuarios reasignados a la nueva empresa correctamente.',
        ]);

    }

    public function single($slack)
    {

        $user = $this->guardManageableUser(User::slack($slack));
        $enterprise = $user->getEnterprise();

        abort_if($enterprise === null, 404, 'El usuario no tiene ninguna empresa asignada.');

        // Soporte gestiona TODAS las empresas — `app('support')` es el propio
        // usuario autenticado, sin relación `enterprises()` (antes null y crasheaba).
        $enterprises = Enterprise::available()->get()->pluck('title', 'slack');
        $enterprises = $enterprises->forget($enterprise->slack);
        $enterprises->prepend('', '');

        return view('supports.views.enterprises.reassigns.single')->with([
            'user' => $user,
            'enterprises' => $enterprises,
            'enterprise' => $enterprise,
        ]);

    }

    public function reassignSingle(Request $request)
    {

        $user = User::slack($request->slack);
        $newEnterprise = Enterprise::slack($request->enterprise);

        if (! $user || ! $newEnterprise) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario o empresa no encontrados.']);
        }

        $this->guardManageableUser($user);

        $enterpriseUser = EnterpriseUser::where('user_id', $user->id)->first();

        if ($enterpriseUser) {

            $enterpriseUser->enterprise_id = $newEnterprise->id;
            $enterpriseUser->save();

            return response()->json([
                'success' => true,
                'enterprise' => $newEnterprise->slack,
                'message' => 'Usuario reasignado a la nueva empresa correctamente.',
            ]);

        }

        return response()->json([
            'success' => false,
            'message' => 'No se encontró la relación del usuario con la empresa.',
        ]);

    }
}
