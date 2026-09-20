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
        // Cambiar el correo o la contraseña son las dos acciones que
        // permitirían tomar la cuenta con una sesión robada (XSS, equipo
        // compartido) sin volver a autenticarse -- se exige la contraseña
        // actual solo en esos dos casos, no en cada guardado del perfil.
        $changesCredentials = $this->filled('password') || $this->input('email') !== $this->user()->email;

        return [
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user()->id)],
            'password' => ['nullable', 'string', 'confirmed', Password::defaults()],
            'password_confirmation' => ['nullable', 'string'],
            'current_password' => $changesCredentials ? ['required', 'current_password'] : ['nullable'],
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
            'current_password.required' => 'Ingresa tu contraseña actual para cambiar el correo o la contraseña.',
            'current_password.current_password' => 'La contraseña actual no es correcta.',
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => 'correo electrónico',
            'password' => 'contraseña',
            'current_password' => 'contraseña actual',
            'cellphone' => 'celular',
            'address' => 'dirección',
        ];
    }
}
