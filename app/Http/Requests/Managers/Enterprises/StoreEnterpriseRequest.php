<?php

namespace App\Http\Requests\Managers\Enterprises;

use Illuminate\Foundation\Http\FormRequest;

class StoreEnterpriseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('enterprises.create');
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:3', 'max:250'],
            'nit' => ['required', 'string', 'min:3', 'max:250'],
            'address' => ['required', 'string', 'min:3', 'max:250'],
            'cellphone' => ['nullable', 'string', 'regex:/^[0-9]{6,10}$/'],
            'email' => ['required', 'email', 'max:250'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título es obligatorio.',
            'nit.required' => 'El NIT es obligatorio.',
            'address.required' => 'La dirección es obligatoria.',
            'cellphone.regex' => 'El celular debe contener solo números (6 a 10 dígitos).',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico no tiene un formato válido.',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'título',
            'nit' => 'NIT',
            'address' => 'dirección',
            'cellphone' => 'celular',
            'email' => 'correo electrónico',
        ];
    }
}
