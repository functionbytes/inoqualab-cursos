<?php

namespace App\Http\Requests\Enterprises;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEnterpriseProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'enterprise';
    }

    public function rules(): array
    {
        return [
            'firstname' => ['required', 'string', 'min:3', 'max:100'],
            'lastname' => ['required', 'string', 'min:3', 'max:100'],
            'identification' => ['nullable', 'string', 'min:3', 'max:100'],
            'address' => ['nullable', 'string', 'min:3', 'max:100'],
            'password' => ['nullable', 'string', 'min:6', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'firstname.required' => 'El nombre es obligatorio.',
            'firstname.min' => 'El nombre debe contener al menos 3 caracteres.',
            'firstname.max' => 'El nombre no puede superar los 100 caracteres.',
            'lastname.required' => 'Los apellidos son obligatorios.',
            'lastname.min' => 'Los apellidos deben contener al menos 3 caracteres.',
            'lastname.max' => 'Los apellidos no pueden superar los 100 caracteres.',
            'identification.min' => 'La identificación debe contener al menos 3 caracteres.',
            'identification.max' => 'La identificación no puede superar los 100 caracteres.',
            'address.min' => 'La dirección debe contener al menos 3 caracteres.',
            'address.max' => 'La dirección no puede superar los 100 caracteres.',
            'password.min' => 'La contraseña debe contener al menos 6 caracteres.',
            'password.max' => 'La contraseña no puede superar los 100 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'firstname' => 'nombres',
            'lastname' => 'apellidos',
            'identification' => 'identificación',
            'address' => 'dirección',
            'password' => 'contraseña',
        ];
    }
}
