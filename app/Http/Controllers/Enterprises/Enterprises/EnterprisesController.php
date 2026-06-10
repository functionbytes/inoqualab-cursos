<?php

namespace App\Http\Controllers\Enterprises\Enterprises;

use App\Http\Controllers\Controller;
use App\Models\Enterprise\Enterprise;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnterprisesController extends Controller
{
    public function index()
    {

        $enterprise = app('enterprise');

        return view('enterprises.views.enterprises.enterprises.edit')->with([
            'enterprise' => $enterprise,
        ]);

    }

    public function update(Request $request): JsonResponse
    {
        $enterprise = app('enterprise');

        if (Enterprise::where('email', $request->email)->where('id', '!=', $enterprise->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'El correo electrónico ya está registrado en nuestro sistema.']);
        }

        $enterprise = Enterprise::slack($request->slack);
        $enterprise->supporting = $request->supporting;
        $enterprise->address = $request->address;
        $enterprise->cellphone = $request->cellphone;
        $enterprise->email = $request->email;
        $enterprise->update();

        return response()->json(['success' => true, 'message' => 'Se ha actualizado correctamente.']);
    }
}
