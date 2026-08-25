<?php

namespace App\Http\Controllers\Managers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Settings\UpdatePortalSettingsRequest;

class PortalSettingsController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->can('settings.update'), 403);

        return view('managers.views.settings.portal.setting');
    }

    public function update(UpdatePortalSettingsRequest $request)
    {
        updateSettings($request->safe()->only([
            'customers_dashboard_variant',
            'customers_courses_variant',
            'customers_nav_layout',
            'customers_certificates_variant',
            'customers_orders_variant',
            'customers_documents_variant',
            'customers_settings_variant',
            'customers_notifications_variant',
            'aula_version',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Se actualizó la apariencia del portal del alumno',
        ]);
    }
}
