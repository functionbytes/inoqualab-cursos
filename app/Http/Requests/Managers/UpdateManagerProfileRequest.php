<?php

namespace App\Http\Requests\Managers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateManagerProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role === 'manager';
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
            'cellphone' => ['nullable', 'string', 'min:6', 'max:20'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore(Auth::id()),
            ],
            'address' => ['nullable', 'string', 'min:3', 'max:150'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
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
            'identification.unique' => 'La identificación ya está registrada en el sistema.',
            'cellphone.min' => 'El celular debe contener al menos 6 caracteres.',
            'cellphone.max' => 'El celular no puede superar los 20 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico no es válido.',
            'email.unique' => 'El correo electrónico ya está registrado en el sistema.',
            'address.min' => 'La dirección debe contener al menos 3 caracteres.',
            'address.max' => 'La dirección no puede superar los 150 caracteres.',
            'avatar.image' => 'El archivo debe ser una imagen.',
            'avatar.mimes' => 'La imagen debe ser JPG, PNG o WEBP.',
            'avatar.max' => 'La imagen no puede superar los 2 MB.',
        ];
    }

    public function attributes(): array
    {
        return [
            'firstname' => 'nombre',
            'lastname' => 'apellidos',
            'identification' => 'identificación',
            'cellphone' => 'celular',
            'email' => 'correo electrónico',
            'address' => 'dirección',
            'avatar' => 'foto de perfil',
        ];
    }
}
