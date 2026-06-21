<?php

namespace App\Http\Requests\Managers\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIncomingMailSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('settings.update');
    }

    public function rules(): array
    {
        return [
            'incoming_mail_confidence_threshold' => ['nullable', 'integer', 'between:0,100'],
        ];
    }

    public function messages(): array
    {
        return [
            'incoming_mail_confidence_threshold.between' => 'El umbral de confianza debe estar entre 0 y 100.',
        ];
    }

    public function attributes(): array
    {
        return [
            'incoming_mail_confidence_threshold' => 'umbral de confianza',
        ];
    }
}
