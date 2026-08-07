<?php

namespace App\Http\Controllers\Supports\Enterprises;

use App\Http\Controllers\Controller;
use App\Models\Enterprise\Enterprise;
use App\Models\Enterprise\EnterpriseUser;
use App\Models\User;
use Illuminate\Http\Request;

class ReassignController extends Controller
{
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

        $users = explode(',', $request->users);
        $newEnterprise = Enterprise::slack($request->enterprise);

        foreach ($users as $identification) {

            // User::identification() aborta con 404 si no hay match -- eso hacía
            // que el chequeo "!$user" de abajo fuera código muerto inalcanzable:
            // una identificación con typo abortaba el request COMPLETO con un
            // 404 crudo en vez de devolver el JSON de error ya escrito para
            // este caso (con el mensaje que sí identifica cuál falló).
            $user = User::where('identification', $identification)->first();

            if (! $user || ! $newEnterprise) {

                return response()->json([
                    'success' => false,
                    'message' => 'Usuario o empresa no encontrados.',
                ]);

            }

            $enterpriseUser = EnterpriseUser::where('user_id', $user->id)->first();

            // Sin esta guarda, un usuario sin fila enterprise_user (2,737 casos
            // reales) crashea con "Attempt to assign property on null".
            if (! $enterpriseUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario '.$identification.' no tiene una empresa asignada para reasignar.',
                ]);
            }

            $enterpriseUser->enterprise_id = $newEnterprise->id;
            $enterpriseUser->save();

        }

        return response()->json([
            'success' => true,
            'enterprise' => $newEnterprise->slack,
            'message' => 'Usuarios reasignados a la nueva empresa correctamente.',
        ]);

    }

    public function single($slack)
    {

        $user = User::slack($slack);
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
