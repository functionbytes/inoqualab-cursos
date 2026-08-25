<?php

namespace App\Http\Requests\Supports\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('users.create');
    }

    public function rules(): array
    {
        return [
            'firstname' => ['required', 'string', 'max:100'],
            'lastname' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'identification' => ['nullable', 'string', 'max:50', Rule::unique('users', 'identification')],
            'cellphone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', 'in:customer,enterprise,distributor,accounting'],
            'password' => ['required', 'string', Password::defaults()],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'El correo electrónico ya está registrado en nuestro sistema.',
            'identification.unique' => 'La identificación ya está registrada en nuestro sistema.',
            'role.in' => 'El rol seleccionado no es válido.',
        ];
    }

    public function attributes(): array
    {
        return [
            'firstname' => 'nombre',
            'lastname' => 'apellido',
            'email' => 'correo electrónico',
            'identification' => 'identificación',
            'cellphone' => 'celular',
            'role' => 'rol',
            'password' => 'contraseña',
        ];
    }
}
