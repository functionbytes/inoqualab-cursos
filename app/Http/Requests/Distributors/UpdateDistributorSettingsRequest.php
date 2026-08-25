<?php

namespace App\Http\Requests\Distributors;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Los datos de la empresa distribuidora se asignaban directamente desde el
 * request: enviar el formulario incompleto metía NULL en columnas NOT NULL
 * (address, cellphone, nit, email…) y el portal respondía 500.
 *
 * El middleware IsDistributor ya garantiza que quien llega es un distribuidor
 * y el controller opera siempre sobre `app('distributor')` — nunca sobre un
 * slack del request — así que esto no protege contra IDOR. Se comprueba el
 * permiso igual por consistencia con la convención del proyecto
 * (.claude/rules/form-requests.md); 'distributor' tiene 'settings.update'
 * en RolesAndPermissionsSeeder, así que no cambia el acceso real.
 */
class UpdateDistributorSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('settings.update');
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:191'],
            'address' => ['required', 'string', 'max:191'],
            'cellphone' => ['required', 'string', 'max:45'],
            'nit' => ['required', 'string', 'max:45'],
            'email' => ['required', 'email', 'max:191'],
            'leading' => ['required', 'string', 'max:191'],
            'supporting' => ['required', 'string', 'max:191'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El nombre de la empresa es obligatorio.',
            'address.required' => 'La dirección es obligatoria.',
            'cellphone.required' => 'El celular es obligatorio.',
            'nit.required' => 'El NIT es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico no es válido.',
            'leading.required' => 'El nombre del responsable es obligatorio.',
            'supporting.required' => 'El nombre del contacto de soporte es obligatorio.',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'nombre de la empresa',
            'leading' => 'responsable',
            'supporting' => 'contacto de soporte',
        ];
    }
}
