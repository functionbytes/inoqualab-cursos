<?php

namespace App\Http\Controllers\Managers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModulesSettingsController extends Controller
{
    public function index()
    {
        return view('managers.views.settings.modules.setting');
    }

    public function update(Request $request): JsonResponse
    {
        abort_unless(auth()->user()->can('settings.update'), 403);
        $modules = [
            'module_coupons',
            'module_bundles',
            'module_incoming_mail',
            'module_invoices',
            'module_departments',
            'module_documents',
            'module_contacts',
            'module_newsletter',
            'module_reviews',
            'module_certifications',
            'module_certifiers',
            'module_enterprises',
            'module_distributors',
            'module_faqs',
            'module_instructions',
            'module_seo',
            'module_analytics',
        ];

        $data = [];
        foreach ($modules as $key) {
            $data[$key] = $request->input($key) == 1 ? '1' : '0';
        }

        updateSettings($data);

        return response()->json([
            'success' => true,
            'message' => 'Configuración de módulos actualizada correctamente.',
        ]);
    }
}
