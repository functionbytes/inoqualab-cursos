<?php

namespace App\Http\Requests\Managers\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAnalyticsNotificationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('settings.update');
    }

    public function rules(): array
    {
        return [
            'sent_emails' => ['nullable', 'array'],
            'sent_emails.*' => ['nullable', 'email', 'max:255'],
            'failed_emails' => ['nullable', 'array'],
            'failed_emails.*' => ['nullable', 'email', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'sent_emails.*.email' => 'Uno de los correos de envío no tiene un formato válido.',
            'failed_emails.*.email' => 'Uno de los correos de fallos no tiene un formato válido.',
        ];
    }
}
