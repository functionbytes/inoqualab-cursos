<?php

namespace App\Http\Requests\Supports;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateSupportProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role === 'support';
    }

    public function rules(): array
    {
        return [
            'firstname' => ['required', 'string', 'min:3', 'max:100'],
            'lastname' => ['required', 'string', 'min:3', 'max:100'],
            'support' => ['required', 'string', 'min:3', 'max:100'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore(Auth::id()),
            ],
            'password' => ['nullable', 'string', 'max:100', Password::defaults()],
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
            'support.required' => 'El nombre de soporte es obligatorio.',
            'support.min' => 'El nombre de soporte debe contener al menos 3 caracteres.',
            'support.max' => 'El nombre de soporte no puede superar los 100 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico no es válido.',
            'email.unique' => 'El correo electrónico ya está registrado en nuestro sistema.',
            'password.min' => 'La contraseña debe contener al menos 6 caracteres.',
            'password.max' => 'La contraseña no puede superar los 100 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'firstname' => 'nombres',
            'lastname' => 'apellidos',
            'support' => 'nombre de soporte',
            'email' => 'correo electrónico',
            'password' => 'contraseña',
        ];
    }
}
