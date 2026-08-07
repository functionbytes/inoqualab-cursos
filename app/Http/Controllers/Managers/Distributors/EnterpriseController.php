<?php

namespace App\Http\Controllers\Managers\Distributors;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Distributors\UpdateDistributorEnterprisesRequest;
use App\Models\Distributor\Distributor;
use App\Models\Enterprise\Enterprise;
use Illuminate\Support\Facades\DB;

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

    public function update(UpdateDistributorEnterprisesRequest $request)
    {
        abort_unless(auth()->user()->can('distributors.update'), 403);

        $data = $request->validated();
        $distributor = Distributor::slack($data['slack']);

        $currentEnterprises = $distributor->enterprises->pluck('id')->toArray();

        $newEnterprises = $data['enterprises'];

        if (! empty($newEnterprises)) {

            $toDetach = array_diff($currentEnterprises, $newEnterprises);

            // sync() en una sola operación atómica dentro de una transacción:
            // el detach()+attach() en loop suelto podía dejar al distribuidor
            // con MENOS empresas que antes y ninguna nueva si un attach() a
            // mitad de camino fallaba (mismo patrón ya corregido en
            // BundlesController::update()).
            DB::transaction(function () use ($distributor, $newEnterprises) {
                $distributor->enterprises()->sync($newEnterprises);
            });

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
