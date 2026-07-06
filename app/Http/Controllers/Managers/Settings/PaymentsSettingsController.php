<?php

namespace App\Http\Controllers\Managers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Settings\UpdatePaymentsSettingsRequest;
use Illuminate\Http\JsonResponse;

class PaymentsSettingsController extends Controller
{
    public function index()
    {
        return view('managers.views.settings.payments.setting');
    }

    public function update(UpdatePaymentsSettingsRequest $request): JsonResponse
    {
        $data['wompi_public_key'] = $request->wompi_public_key;
        $data['wompi_sandbox'] = $request->has('wompi_sandbox') ? 'true' : 'false';

        // Los secretos solo se actualizan si se envía un valor nuevo (el input va
        // enmascarado). Un envío vacío conserva el secreto guardado, no lo borra.
        if ($request->filled('wompi_integrity_secret')) {
            $data['wompi_integrity_secret'] = $request->wompi_integrity_secret;
        }
        if ($request->filled('wompi_events_secret')) {
            $data['wompi_events_secret'] = $request->wompi_events_secret;
        }

        updateSettings($data);

        return response()->json([
            'success' => true,
            'message' => 'Configuración de pagos actualizada correctamente',
        ]);
    }
}
