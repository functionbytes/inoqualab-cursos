<?php

namespace App\Http\Controllers\Managers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentsSettingsController extends Controller
{
    public function index()
    {
        return view('managers.views.settings.payments.setting');
    }

    public function update(Request $request)
    {
        $request->validate([
            'wompi_public_key' => ['required', 'string', 'max:255'],
            'wompi_integrity_secret' => ['required', 'string', 'max:255'],
            'wompi_events_secret' => ['required', 'string', 'max:255'],
        ]);

        $data['wompi_public_key'] = $request->wompi_public_key;
        $data['wompi_integrity_secret'] = $request->wompi_integrity_secret;
        $data['wompi_events_secret'] = $request->wompi_events_secret;
        $data['wompi_sandbox'] = $request->has('wompi_sandbox') ? 'true' : 'false';

        updateSettings($data);

        return response()->json([
            'success' => true,
            'message' => 'Configuración de pagos actualizada correctamente',
        ]);
    }
}
