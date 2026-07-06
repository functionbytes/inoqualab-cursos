<?php

namespace App\Http\Controllers\Accountings\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accountings\UpdateAccountingProfileRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    public function index()
    {

        $user = User::auth();

        return view('accountings.views.settings.profile.setting')->with([
            'user' => $user,
        ]);
    }

    public function update(UpdateAccountingProfileRequest $request): JsonResponse
    {
        // Ajustes de perfil: SIEMPRE el usuario autenticado, nunca el slack del
        // request (evita toma de cuenta: editar/resetear password de cualquiera).
        $user = auth()->user();

        $user->firstname = Str::upper($request->firstname);
        $user->lastname = Str::upper($request->lastname);
        $user->cellphone = $request->cellphone;
        $user->email = $request->email;

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

        return response()->json(['success' => true, 'message' => 'Se ha actualizado correctamente el perfil.']);
    }
}
