<?php

namespace App\Http\Controllers\Managers\Distributors;

use App\Http\Controllers\Controller;
use App\Models\Distributor\Distributor;
use App\Models\Enterprise\Enterprise;
use Illuminate\Http\Request;

class EnterpriseController extends Controller
{
    public function index($slack)
    {

        $distributor = Distributor::slack($slack);

        $enterprise = $distributor->enterprises;

        $enterprises = Enterprise::available()->get();
        $enterprises = $enterprises->pluck('title', 'id');

        return view('managers.views.distributors.enterprises.index')->with([
            'distributor' => $distributor,
            'enterprises' => $enterprises,
            'enterprise' => $enterprise,
        ]);

    }

    public function update(Request $request)
    {

        $distributor = Distributor::slack($request->slack);

        if (! $distributor) {

            return response()->json([
                'success' => false,
                'message' => 'Distribuidor no encontrado.',
            ]);
        }

        $currentEnterprises = $distributor->enterprises->pluck('id')->toArray();

        $newEnterprises = $request->enterprises ? explode(',', $request->enterprises) : [];

        if (! empty($newEnterprises)) {

            $toDetach = array_diff($currentEnterprises, $newEnterprises);
            $distributor->enterprises()->detach($toDetach);

            foreach ($newEnterprises as $id) {
                if (! in_array($id, $currentEnterprises)) {
                    $distributor->enterprises()->attach($id);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Se asignaron las empresas seleccionadas.',
                'detached_enterprises' => $toDetach,
                'attached_enterprises' => array_diff($newEnterprises, $currentEnterprises),
            ]);

        }

        return response()->json([
            'success' => false,
            'message' => 'No se lograron reasignar las empresas seleccionadas.',
        ]);

    }
}
