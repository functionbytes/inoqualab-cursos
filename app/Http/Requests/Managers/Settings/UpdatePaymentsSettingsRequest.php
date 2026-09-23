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
            // Dos juegos de credenciales (sandbox/producción), ambos opcionales:
            // el toggle "wompi_sandbox" decide cuál usa WompiService, y el
            // comerciante puede no tener aún credenciales de producción reales.
            'wompi_public_key_sandbox' => ['nullable', 'string', 'max:255'],
            'wompi_public_key_production' => ['nullable', 'string', 'max:255'],
            // Secretos enmascarados: vacío = conservar el guardado (el controller
            // solo actualiza si se envía un valor nuevo).
            'wompi_integrity_secret_sandbox' => ['nullable', 'string', 'max:255'],
            'wompi_events_secret_sandbox' => ['nullable', 'string', 'max:255'],
            'wompi_integrity_secret_production' => ['nullable', 'string', 'max:255'],
            'wompi_events_secret_production' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'wompi_public_key_sandbox' => 'llave pública (sandbox)',
            'wompi_public_key_production' => 'llave pública (producción)',
            'wompi_integrity_secret_sandbox' => 'secreto de integridad (sandbox)',
            'wompi_events_secret_sandbox' => 'secreto de eventos (sandbox)',
            'wompi_integrity_secret_production' => 'secreto de integridad (producción)',
            'wompi_events_secret_production' => 'secreto de eventos (producción)',
        ];
    }
}
