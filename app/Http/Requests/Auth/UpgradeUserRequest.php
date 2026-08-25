<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpgradeUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // La ruta exige middleware auth; solo el propio usuario completa sus datos.
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'firstname' => ['required', 'string', 'min:2', 'max:200'],
            'lastname' => ['required', 'string', 'min:2', 'max:200'],
            'cellphone' => ['required', 'numeric', 'digits_between:8,20'],
            // La ciudad debe existir; el controller la mapea a citie_id.
            'citie' => ['required', 'integer', 'exists:cities,id'],
            'address' => ['required', 'string', 'min:10', 'max:500'],
            'identification' => ['required', 'string', 'max:50'],
            // Unicidad ignorando al propio usuario: sin esto un upgrade podía tomar
            // el correo de otra cuenta.
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user()?->id)],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ];
    }

    public function messages(): array
    {
        return [
            'firstname.required' => 'El nombre es obligatorio.',
            'firstname.min' => 'El nombre debe tener al menos 2 caracteres.',
            'firstname.max' => 'El nombre no puede superar los 200 caracteres.',
            'lastname.required' => 'El apellido es obligatorio.',
            'lastname.min' => 'El apellido debe tener al menos 2 caracteres.',
            'lastname.max' => 'El apellido no puede superar los 200 caracteres.',
            'cellphone.required' => 'El número de celular es obligatorio.',
            'cellphone.numeric' => 'El número de celular solo puede contener dígitos.',
            'cellphone.digits_between' => 'El número de celular debe tener entre 8 y 20 dígitos.',
            'citie.required' => 'La ciudad es obligatoria.',
            'address.required' => 'La dirección es obligatoria.',
            'address.min' => 'La dirección debe tener al menos 10 caracteres.',
            'address.max' => 'La dirección no puede superar los 500 caracteres.',
            'identification.required' => 'El número de identificación es obligatorio.',
            'identification.max' => 'La identificación no puede superar los 50 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico no tiene un formato válido.',
            'email.max' => 'El correo electrónico no puede superar los 255 caracteres.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ];
    }

    public function attributes(): array
    {
        return [
            'firstname' => 'nombre',
            'lastname' => 'apellido',
            'cellphone' => 'celular',
            'citie' => 'ciudad',
            'address' => 'dirección',
            'identification' => 'identificación',
            'email' => 'correo electrónico',
            'password' => 'contraseña',
        ];
    }
}
