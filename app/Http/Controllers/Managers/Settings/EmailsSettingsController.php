<?php

namespace App\Http\Controllers\Managers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Settings\UpdateEmailsSettingsRequest;
use Illuminate\Http\JsonResponse;

class EmailsSettingsController extends Controller
{
    public function index()
    {
        return view('managers.views.settings.emails.setting');
    }

    public function update(UpdateEmailsSettingsRequest $request): JsonResponse
    {
        $data['imap_status'] = $request->has('imap_status') ? 'true' : 'false';
        $data['imap_host'] = $request->imap_host;
        $data['imap_port'] = $request->imap_port;
        $data['imap_protocol'] = $request->imap_protocol;
        $data['imap_username'] = $request->imap_username;
        $data['imap_password'] = $request->imap_password;
        $data['imap_encryption'] = $request->imap_encryption;

        $data['mail_status'] = $request->has('mail_status') ? 'true' : 'false';
        $data['mail_host'] = $request->mail_host;
        $data['mail_port'] = $request->mail_port;
        $data['mail_encryption'] = $request->mail_encryption;
        $data['mail_username'] = $request->mail_username;
        $data['mail_from_address'] = $request->mail_from_address;
        $data['mail_from_name'] = $request->mail_from_name;

        // Solo actualizar contraseña si se envió un valor
        if ($request->filled('mail_password')) {
            $data['mail_password'] = $request->mail_password;
        }

        updateSettings($data);

        return response()->json([
            'success' => true,
            'message' => 'Configuración de correo actualizada correctamente.',
        ]);
    }
}
