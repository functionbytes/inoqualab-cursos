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
        $distributor = app('support');
        $enterprises = $distributor->enterprises ?? collect(); // Asegurar que sea una colección
        $users = $enterprise->users()->available()->get();

        // Asegurar que la colección no sea nula antes de llamar a prepend
        if ($enterprises->isNotEmpty()) {
            $enterprises->prepend('', '');
        }

        $enterprises = $enterprises->pluck('title', 'slack');
        $users = $users->pluck('identification', 'identification');

        // Verificar si la clave existe antes de eliminarla
        if ($enterprises->has($enterprise->slack)) {
            $enterprises = $enterprises->forget($enterprise->slack);
        }

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

            $user = User::identification($identification);

            if (! $user || ! $newEnterprise) {

                return response()->json([
                    'success' => false,
                    'message' => 'Usuario o empresa no encontrados.',
                ]);

            }

            $enterpriseUser = EnterpriseUser::where('user_id', $user->id)->first();

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

        $distributor = app('support');
        $enterprises = $distributor->enterprises;
        $enterprises->prepend('', '');
        $enterprises = $enterprises->pluck('title', 'slack');

        $enterprises = $enterprises->forget($enterprise->slack);

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
