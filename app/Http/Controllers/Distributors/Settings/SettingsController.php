<?php

namespace App\Http\Controllers\Distributors\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Distributors\UpdateDistributorProfileRequest;
use App\Http\Requests\Distributors\UpdateDistributorSettingsRequest;
use App\Models\Distributor\Distributor;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    public function profile()
    {

        $user = User::auth();

        return view('distributors.views.settings.profile.setting')->with([
            'user' => $user,
        ]);
    }

    public function distributor()
    {

        $user = User::auth();
        $distributor = $user->relationsDistributor;

        return view('distributors.views.settings.distributor.setting')->with([
            'distributor' => $distributor,
        ]);

    }

    public function notifications()
    {

        $distributor = app('distributor');
        $user = User::auth();

        return view('distributors.views.settings.setting.setting')->with([
            'user' => $user,
            'distributor' => $distributor,
        ]);

    }

    public function updateDistributor(UpdateDistributorSettingsRequest $request): JsonResponse
    {
        // Ajustes: SIEMPRE el distribuidor autenticado, nunca el slack del request.
        $distributor = app('distributor');

        if (Distributor::where('email', $request->email)->where('id', '!=', $distributor->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'El correo electrónico ya está registrado en nuestro sistema.']);
        }

        if (Distributor::where('nit', $request->nit)->where('id', '!=', $distributor->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'El NIT ya está registrado en nuestro sistema.']);
        }

        $distributor->title = Str::upper($request->title);
        $distributor->slug = Str::slug($request->title, '-');
        $distributor->address = $request->address;
        $distributor->cellphone = $request->cellphone;
        $distributor->nit = $request->nit;
        $distributor->leading = $request->leading;
        $distributor->supporting = $request->supporting;
        $distributor->email = $request->email;

        if ($distributor->isDirty()) {
            $distributor->update();

            activity()
                ->performedOn($distributor)
                ->withProperties($distributor->getChanges())
                ->log('updated');
        }

        return response()->json(['success' => true, 'message' => 'Se ha actualizado correctamente el distribuidor.']);
    }

    public function updateUser(UpdateDistributorProfileRequest $request): JsonResponse
    {
        // Ajustes de perfil: SIEMPRE el usuario autenticado, nunca el slack del
        // request (evita toma de cuenta: editar/resetear password de cualquiera).
        $user = auth()->user();

        $user->firstname = Str::upper($request->firstname);
        $user->lastname = Str::upper($request->lastname);
        $user->identification = $request->identification;
        $user->cellphone = $request->cellphone;
        $user->email = $request->email;
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

        return response()->json(['success' => true, 'message' => 'Se ha actualizado correctamente el perfil.']);
    }

    public function updateNotifications(Request $request)
    {
        // Ajustes: SIEMPRE el distribuidor autenticado, nunca el slack del request.
        $distributor = app('distributor');

        $distributor->mail_notification = $request->mail_notification == 'true' ? 1 : 0;
        $distributor->inscription_notification = $request->inscription_notification == 'true' ? 1 : 0;
        $distributor->invoice_notification = $request->invoice_notification == 'true' ? 1 : 0;

        if ($distributor->isDirty()) {

            $distributor->update();

            activity()
                ->performedOn($distributor)
                ->withProperties($distributor->getChanges())
                ->log('updated');
        }

        $response = [
            'success' => true,
            'message' => 'Se ha actualizado correctamente',
        ];

        return response()->json($response);

    }
}
