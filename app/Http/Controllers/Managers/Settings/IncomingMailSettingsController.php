<?php

namespace App\Http\Controllers\Managers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IncomingMailSettingsController extends Controller
{
    public function index()
    {
        return view('managers.views.settings.incoming-mail.setting');
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'incoming_mail_confidence_threshold' => ['nullable', 'integer', 'between:0,100'],
        ]);

        $data = [
            'incoming_mail_enabled' => $request->has('incoming_mail_enabled') ? 'true' : 'false',
            'incoming_mail_auto_process' => $request->has('incoming_mail_auto_process') ? 'true' : 'false',
            'incoming_mail_confidence_threshold' => (int) $request->input('incoming_mail_confidence_threshold', 90),
            'incoming_mail_trust_all_senders' => $request->has('incoming_mail_trust_all_senders') ? 'true' : 'false',
        ];

        updateSettings($data);

        return response()->json([
            'success' => true,
            'message' => 'Configuración de correos entrantes actualizada',
        ]);
    }
}
