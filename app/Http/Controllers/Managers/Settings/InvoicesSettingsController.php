<?php

namespace App\Http\Controllers\Managers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InvoicesSettingsController extends Controller
{
    public function index()
    {
        return view('managers.views.settings.invoices.setting')->with([
        ]);
    }

    public function update(Request $request)
    {
        abort_unless(auth()->user()->can('settings.update'), 403);

        $data['invoices_notification_email_enable'] = $request->invoices_notification_email_enable;
        $data['invoice_default'] = $request->invoice_default;
        $data['invoice_days'] = $request->invoice_days;

        updateSettings($data);

        return response()->json([
            'success' => true,
            'message' => 'Se actualizo correctamente la configuración',
        ]);

    }
}
