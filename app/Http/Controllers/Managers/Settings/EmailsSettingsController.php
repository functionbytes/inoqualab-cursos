<?php

namespace App\Http\Controllers\Managers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmailsSettingsController extends Controller
{
    public function index()
    {

        return view('managers.views.settings.emails.setting')->with([
        ]);

    }

    public function update(Request $request)
    {
        $request->validate([
            'imap_host' => ['nullable', 'string', 'max:255'],
            'imap_port' => ['nullable', 'integer', 'between:1,65535'],
            'imap_protocol' => ['nullable', 'string', 'in:imap,pop3'],
            'imap_username' => ['nullable', 'string', 'max:255'],
            'imap_password' => ['nullable', 'string', 'max:255'],
            'imap_encryption' => ['nullable', 'string', 'in:ssl,tls,notls'],
        ]);

        $data['imap_status'] = $request->imap_status;
        $data['imap_host'] = $request->imap_host;
        $data['imap_port'] = $request->imap_port;
        $data['imap_protocol'] = $request->imap_protocol;
        $data['imap_username'] = $request->imap_username;
        $data['imap_password'] = $request->imap_password;
        $data['imap_encryption'] = $request->imap_encryption;

        updateSettings($data);

        return response()->json([
            'success' => true,
            'message' => 'Se actualizo correctamente configuración de correo',
        ]);

    }
}
