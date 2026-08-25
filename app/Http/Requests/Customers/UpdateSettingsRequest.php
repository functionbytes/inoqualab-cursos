<?php

namespace App\Http\Requests\Customers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateSettingsRequest extends FormRequest
{
    /**
     * Autoservicio: el cliente edita su propia cuenta (`$this->user()->id`
     * en la regla `unique`). El portal Customers no usa permisos Spatie
     * (ver StoreReviewRequest hermano) — no hay nada que comprobar con
     * `can()` para "editar mis propios datos".
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user()->id)],
            'password' => ['nullable', 'string', 'confirmed', Password::defaults()],
            'password_confirmation' => ['nullable', 'string'],
            'cellphone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'El correo no tiene un formato válido.',
            'email.unique' => 'Ese correo ya está registrado.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => 'correo electrónico',
            'password' => 'contraseña',
            'cellphone' => 'celular',
            'address' => 'dirección',
        ];
    }
}
