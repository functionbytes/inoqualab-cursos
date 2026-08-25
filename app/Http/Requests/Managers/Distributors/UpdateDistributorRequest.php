<?php

namespace App\Http\Requests\Managers\Distributors;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDistributorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('distributors.update');
    }

    public function rules(): array
    {
        return [
            'slack' => ['required', 'string', 'exists:distributors,slack'],
            'title' => ['required', 'string', 'min:3', 'max:250'],
            'nit' => ['required', 'string', 'min:3', 'max:250'],
            'address' => ['required', 'string', 'min:3', 'max:250'],
            'cellphone' => ['required', 'string', 'regex:/^[0-9]{6,10}$/'],
            'email' => ['required', 'email', 'max:250'],
            'leading' => ['required', 'string', 'min:3', 'max:250'],
            'supporting' => ['required', 'string', 'min:3', 'max:250'],
            'enterprise_generate' => ['nullable', 'in:0,1'],
            'available' => ['required', 'in:0,1'],
        ];
    }

    public function messages(): array
    {
        return [
            'slack.required' => 'El distribuidor es obligatorio.',
            'slack.exists' => 'El distribuidor indicado no existe.',
            'title.required' => 'El título es obligatorio.',
            'nit.required' => 'El NIT es obligatorio.',
            'address.required' => 'La dirección es obligatoria.',
            'cellphone.required' => 'El celular es obligatorio.',
            'cellphone.regex' => 'El celular debe contener solo números (6 a 10 dígitos).',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico no tiene un formato válido.',
            'leading.required' => 'El gerente encargado es obligatorio.',
            'supporting.required' => 'El soporte encargado es obligatorio.',
            'enterprise_generate.in' => 'El permiso de empresa seleccionado no es válido.',
            'available.required' => 'El estado es obligatorio.',
            'available.in' => 'El estado seleccionado no es válido.',
        ];
    }

    public function attributes(): array
    {
        return [
            'slack' => 'distribuidor',
            'title' => 'título',
            'nit' => 'NIT',
            'address' => 'dirección',
            'cellphone' => 'celular',
            'email' => 'correo electrónico',
            'leading' => 'gerente',
            'supporting' => 'soporte',
            'enterprise_generate' => 'permisos empresa',
            'available' => 'estado',
        ];
    }
}
