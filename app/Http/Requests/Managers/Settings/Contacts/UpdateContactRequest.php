<?php

namespace App\Http\Requests\Managers\Settings\Contacts;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('contacts.update');
    }

    public function rules(): array
    {
        return [
            'slack' => ['required', 'string', 'exists:contacts,slack'],
            'reviewed' => ['required', 'in:0,1'],
        ];
    }

    public function messages(): array
    {
        return [
            'slack.required' => 'El contacto es obligatorio.',
            'slack.exists' => 'El contacto seleccionado no existe.',
            'reviewed.required' => 'El estado es obligatorio.',
            'reviewed.in' => 'El estado debe ser Gestionado o Pendiente.',
        ];
    }

    public function attributes(): array
    {
        return [
            'slack' => 'contacto',
            'reviewed' => 'estado',
        ];
    }
}
