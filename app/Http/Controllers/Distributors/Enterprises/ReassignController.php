<?php

namespace App\Http\Controllers\Distributors\Enterprises;

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
        $distributor = app('distributor');
        $enterprises = $distributor->enterprises;
        $users = $enterprise->users()->available()->get();
        $enterprises->prepend('', '');
        $enterprises = $enterprises->pluck('title', 'slack');
        $users = $users->pluck('identification', 'identification');

        $enterprises = $enterprises->forget($enterprise->slack);

        return view('distributors.views.enterprises.reassigns.all')->with([
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
                    'message' => 'Usuario o empresa no encontrados.']);
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

        $distributor = app('distributor');
        $enterprises = $distributor->enterprises;
        $enterprises->prepend('', '');
        $enterprises = $enterprises->pluck('title', 'slack');

        $enterprises = $enterprises->forget($enterprise->slack);

        return view('distributors.views.enterprises.reassigns.single')->with([
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
                'message' => 'Usuario o empresa no encontrados.',
            ]);
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
