<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customers\UpdateSettingsRequest;
use App\Models\Citie;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $user = app('customer');
        $citie = $user->citie_id;
        $cities = $user->citie_id
            ? Citie::where('id', $user->citie_id)->pluck('title', 'id')
            : collect();

        // El carnet y la barra de perfil completo de la pantalla necesitan
        // saber cuanto lleva matriculado el alumno y cuanto ha conseguido.
        $user->loadCount(['inscriptions', 'certificates']);

        $variant = portalVariant('customers_settings_variant');

        return view('customers.views.settings.index'.$variant, compact('user', 'cities', 'citie'));
    }

    public function update(UpdateSettingsRequest $request): JsonResponse
    {
        $user = app('customer');
        $validated = $request->validated();

        if ($request->filled('password')) {
            $user->password = $validated['password'];
        }

        $user->cellphone = $validated['cellphone'] ?? $user->cellphone;
        $user->email = $validated['email'];
        $user->address = $validated['address'] ?? $user->address;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Se ha actualizado correctamente',
        ]);
    }
}
