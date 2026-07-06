<?php

namespace App\Http\Requests\Distributors;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateDistributorProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role === 'distributor';
    }

    public function rules(): array
    {
        return [
            'firstname' => ['required', 'string', 'min:3', 'max:100'],
            'lastname' => ['required', 'string', 'min:3', 'max:100'],
            'identification' => [
                'nullable', 'string', 'min:3', 'max:100',
                Rule::unique('users', 'identification')->ignore(Auth::id()),
            ],
            'cellphone' => ['nullable', 'string', 'min:6', 'max:10'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore(Auth::id()),
            ],
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
            'identification.unique' => 'La identificación ya está registrada en nuestro sistema.',
            'cellphone.min' => 'El celular debe contener al menos 6 caracteres.',
            'cellphone.max' => 'El celular no puede superar los 10 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico no es válido.',
            'email.unique' => 'El correo electrónico ya está registrado en nuestro sistema.',
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
            'cellphone' => 'celular',
            'email' => 'correo electrónico',
            'address' => 'dirección',
            'password' => 'contraseña',
        ];
    }
}
