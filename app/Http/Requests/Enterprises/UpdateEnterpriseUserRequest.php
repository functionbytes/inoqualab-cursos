<?php

namespace App\Http\Requests\Enterprises;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEnterpriseUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('users.update');
    }

    public function rules(): array
    {
        // Ownership ya se resuelve en el controller (firstOrFail contra la
        // empresa autenticada); aquí solo se ignora el propio id en unique.
        $userId = app('enterprise')->users()->where('users.slack', $this->slack)->value('users.id');

        return [
            'firstname' => ['required', 'string', 'max:191'],
            'lastname' => ['required', 'string', 'max:191'],
            'identification' => [
                'nullable', 'string', 'max:191',
                Rule::unique('users', 'identification')->ignore($userId),
            ],
            'cellphone' => ['nullable', 'string', 'max:191'],
            'email' => [
                'required', 'string', 'email', 'max:191',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'address' => ['nullable', 'string', 'max:191'],
            'company' => ['nullable', 'string', 'max:191'],
            'available' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:6', 'max:191'],
        ];
    }

    public function messages(): array
    {
        return [
            'firstname.required' => 'El nombre es obligatorio.',
            'firstname.max' => 'El nombre no puede superar los 191 caracteres.',
            'lastname.required' => 'Los apellidos son obligatorios.',
            'lastname.max' => 'Los apellidos no pueden superar los 191 caracteres.',
            'identification.unique' => 'La identificación ya está registrada en nuestro sistema.',
            'identification.max' => 'La identificación no puede superar los 191 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico no tiene un formato válido.',
            'email.unique' => 'El correo electrónico ya está registrado en nuestro sistema.',
            'email.max' => 'El correo electrónico no puede superar los 191 caracteres.',
            'password.min' => 'La contraseña debe contener al menos 6 caracteres.',
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
            'company' => 'empresa',
            'available' => 'estado',
            'password' => 'contraseña',
        ];
    }
}
