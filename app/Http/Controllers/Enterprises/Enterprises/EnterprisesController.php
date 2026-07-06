<?php

namespace App\Http\Controllers\Enterprises\Enterprises;

use App\Http\Controllers\Controller;
use App\Http\Requests\Enterprises\UpdateEnterpriseRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class EnterprisesController extends Controller
{
    public function index(): View
    {

        $enterprise = app('enterprise');

        return view('enterprises.views.enterprises.enterprises.edit')->with([
            'enterprise' => $enterprise,
        ]);

    }

    public function update(UpdateEnterpriseRequest $request): JsonResponse
    {
        // Ownership: se actualiza SIEMPRE la empresa autenticada, nunca la del
        // slack del request (evita IDOR: editar la empresa de otro por slack).
        $enterprise = app('enterprise');

        $enterprise->supporting = $request->supporting;
        $enterprise->address = $request->address;
        $enterprise->cellphone = $request->cellphone;
        $enterprise->email = $request->email;
        $enterprise->update();

        return response()->json(['success' => true, 'message' => 'Se ha actualizado correctamente.']);
    }
}
