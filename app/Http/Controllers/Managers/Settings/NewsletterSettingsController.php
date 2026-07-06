<?php

namespace App\Http\Controllers\Managers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Settings\UpdateNewsletterSettingsRequest;
use Illuminate\Http\JsonResponse;

class NewsletterSettingsController extends Controller
{
    public function index()
    {
        return view('managers.views.settings.newsletter.setting');
    }

    public function update(UpdateNewsletterSettingsRequest $request): JsonResponse
    {
        $data = [
            'newsletter_enabled' => $request->newsletter_enabled == 1 ? 1 : 0,
            'newsletter_double_optin' => $request->newsletter_double_optin == 1 ? 1 : 0,
            'newsletter_email_notifications' => $request->newsletter_email_notifications == 1 ? 1 : 0,
            'newsletter_notification_email' => $request->input('newsletter_notification_email', ''),
            'newsletter_popup_enabled' => $request->newsletter_popup_enabled == 1 ? 1 : 0,
            'newsletter_popup_delay' => max(0, (int) $request->input('newsletter_popup_delay', 2)),
            'newsletter_mailjet_enabled' => $request->newsletter_mailjet_enabled == 1 ? 1 : 0,
            'newsletter_mailjet_api_key' => $request->input('newsletter_mailjet_api_key', ''),
            'newsletter_mailjet_list_id' => $request->input('newsletter_mailjet_list_id', ''),
        ];

        // El secreto va enmascarado en la vista: solo se actualiza si se envía uno
        // nuevo; un envío vacío conserva el secreto guardado.
        if ($request->filled('newsletter_mailjet_api_secret')) {
            $data['newsletter_mailjet_api_secret'] = $request->input('newsletter_mailjet_api_secret');
        }

        updateSettings($data);

        return response()->json([
            'success' => true,
            'message' => 'Configuración de newsletter actualizada correctamente.',
        ]);
    }
}
