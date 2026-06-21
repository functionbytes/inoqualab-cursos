<?php

namespace App\Http\Requests\Managers\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentsSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('settings.update');
    }

    public function rules(): array
    {
        return [
            'wompi_public_key' => ['required', 'string', 'max:255'],
            'wompi_integrity_secret' => ['required', 'string', 'max:255'],
            'wompi_events_secret' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'wompi_public_key.required' => 'La llave pública de Wompi es obligatoria.',
            'wompi_integrity_secret.required' => 'El secreto de integridad de Wompi es obligatorio.',
            'wompi_events_secret.required' => 'El secreto de eventos de Wompi es obligatorio.',
        ];
    }

    public function attributes(): array
    {
        return [
            'wompi_public_key' => 'llave pública',
            'wompi_integrity_secret' => 'secreto de integridad',
            'wompi_events_secret' => 'secreto de eventos',
        ];
    }
}
