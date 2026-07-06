<?php

namespace App\Http\Requests\Managers\Distributors;

use Illuminate\Foundation\Http\FormRequest;

class StoreDistributorStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'distributor' => ['required', 'string', 'exists:distributors,slack'],
            'firstname' => ['required', 'string', 'min:3', 'max:100'],
            'lastname' => ['required', 'string', 'min:3', 'max:100'],
            'identification' => ['nullable', 'string', 'min:3', 'max:100'],
            'cellphone' => ['nullable', 'string', 'regex:/^[0-9]{6,10}$/'],
            'email' => ['required', 'email', 'max:250'],
            'address' => ['nullable', 'string', 'max:250'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }

    public function messages(): array
    {
        return [
            'distributor.required' => 'El distribuidor es obligatorio.',
            'distributor.exists' => 'El distribuidor indicado no existe.',
            'firstname.required' => 'El nombre es obligatorio.',
            'lastname.required' => 'El apellido es obligatorio.',
            'cellphone.regex' => 'El celular debe contener solo números (6 a 10 dígitos).',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico no tiene un formato válido.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'distributor' => 'distribuidor',
            'firstname' => 'nombre',
            'lastname' => 'apellido',
            'identification' => 'identificación',
            'cellphone' => 'celular',
            'email' => 'correo electrónico',
            'address' => 'dirección',
            'password' => 'contraseña',
        ];
    }
}
