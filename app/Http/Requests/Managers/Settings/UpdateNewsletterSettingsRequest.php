<?php

namespace App\Http\Requests\Managers\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNewsletterSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('settings.update');
    }

    public function rules(): array
    {
        return [
            'newsletter_notification_email' => ['nullable', 'email', 'max:191'],
            'newsletter_popup_delay' => ['nullable', 'integer', 'min:0', 'max:60'],
            'newsletter_mailjet_api_key' => ['nullable', 'string', 'max:255'],
            'newsletter_mailjet_api_secret' => ['nullable', 'string', 'max:255'],
            'newsletter_mailjet_list_id' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'newsletter_notification_email.email' => 'El correo de notificación no tiene un formato válido.',
            'newsletter_popup_delay.min' => 'El retraso del popup no puede ser negativo.',
            'newsletter_popup_delay.max' => 'El retraso del popup no puede superar 60 segundos.',
        ];
    }

    public function attributes(): array
    {
        return [
            'newsletter_notification_email' => 'correo de notificación',
            'newsletter_popup_delay' => 'retraso del popup',
            'newsletter_mailjet_api_key' => 'API key de Mailjet',
            'newsletter_mailjet_api_secret' => 'API secret de Mailjet',
            'newsletter_mailjet_list_id' => 'ID de lista de Mailjet',
        ];
    }
}
