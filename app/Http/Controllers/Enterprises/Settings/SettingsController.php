<?php

namespace App\Http\Controllers\Enterprises\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Enterprises\UpdateEnterpriseProfileRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    public function index()
    {

        $user = User::auth();

        return view('enterprises.views.settings.edit')->with([
            'user' => $user,
        ]);

    }

    public function update(UpdateEnterpriseProfileRequest $request): JsonResponse
    {
        // Ajustes de perfil: SIEMPRE el usuario autenticado, nunca un slack del
        // request (evita toma de cuenta: editar/resetear password de cualquiera).
        $user = User::auth();

        $user->firstname = Str::upper($request->firstname);
        $user->lastname = Str::upper($request->lastname);
        $user->identification = $request->identification;
        $user->address = $request->address;

        if ($request->filled('password')) {
            $user->password = $request->password;
        }

        if ($user->isDirty()) {
            $user->save();

            $changes = $user->getChanges();
            unset($changes['password'], $changes['updated_at']);

            activity()
                ->performedOn($user)
                ->withProperties($changes)
                ->log('updated');
        }

        return response()->json([
            'success' => true,
            'message' => 'Se ha actualizado correctamente',
        ]);

    }
}
