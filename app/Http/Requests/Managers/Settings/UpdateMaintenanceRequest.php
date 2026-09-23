<?php

namespace App\Http\Requests\Managers\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('settings.update');
    }

    public function rules(): array
    {
        // Un secreto vacío dejaría el sitio sin bypass usable: se exige uno
        // válido (mínimo 8 chars, sin espacios) solo cuando se activa el modo
        // mantenimiento, no cuando se desactiva.
        if ($this->input('maintenance_mode') == 'true') {
            return [
                'maintenance_mode_value' => ['required', 'string', 'min:8', 'regex:/^\S+$/'],
            ];
        }

        return [];
    }

    public function messages(): array
    {
        return [
            'maintenance_mode_value.required' => 'La llave de acceso es obligatoria para activar el mantenimiento.',
            'maintenance_mode_value.min' => 'La llave de acceso debe tener al menos 8 caracteres.',
            'maintenance_mode_value.regex' => 'La llave de acceso no puede contener espacios.',
        ];
    }
}
