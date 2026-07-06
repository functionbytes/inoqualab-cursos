<?php

namespace App\Http\Requests\Managers\Distributors;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDistributorStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'slack' => ['required', 'string', 'exists:users,slack'],
            'firstname' => ['required', 'string', 'min:3', 'max:100'],
            'lastname' => ['required', 'string', 'min:3', 'max:100'],
            'identification' => ['nullable', 'string', 'min:3', 'max:100'],
            'cellphone' => ['nullable', 'string', 'regex:/^[0-9]{6,10}$/'],
            'email' => ['required', 'email', 'max:250'],
            'address' => ['nullable', 'string', 'max:250'],
            'available' => ['required', 'in:0,1'],
            'password' => ['nullable', 'string', 'min:8'],
        ];
    }

    public function messages(): array
    {
        return [
            'slack.required' => 'El empleado es obligatorio.',
            'slack.exists' => 'El empleado indicado no existe.',
            'firstname.required' => 'El nombre es obligatorio.',
            'lastname.required' => 'El apellido es obligatorio.',
            'cellphone.regex' => 'El celular debe contener solo números (6 a 10 dígitos).',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico no tiene un formato válido.',
            'available.required' => 'El estado es obligatorio.',
            'available.in' => 'El estado seleccionado no es válido.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'slack' => 'empleado',
            'firstname' => 'nombre',
            'lastname' => 'apellido',
            'identification' => 'identificación',
            'cellphone' => 'celular',
            'email' => 'correo electrónico',
            'address' => 'dirección',
            'available' => 'estado',
            'password' => 'contraseña',
        ];
    }
}
