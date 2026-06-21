<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normaliza el celular a 10 dígitos colombianos (quita +57, 57, espacios).
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('cellphone')) {
            $cel = preg_replace('/\D/', '', (string) $this->cellphone);
            $cel = preg_replace('/^57/', '', $cel);
            $this->merge(['cellphone' => substr((string) $cel, 0, 10)]);
        }
    }

    public function rules(): array
    {
        return [
            'firstname' => ['required', 'string', 'max:100'],
            'lastname' => ['required', 'string', 'max:100'],
            'identification_type' => ['required', 'in:CC,CE,TI,NIT,PAS,PEP'],
            'identification' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255'],
            'cellphone' => ['required', 'regex:/^3\d{9}$/'],
            'company' => ['nullable', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'citie' => ['nullable', 'integer', 'exists:cities,id'],
            'password' => ['nullable', 'string', 'min:8'],
            'terms' => ['accepted'],
            'newsletter' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'firstname.required' => 'El nombre es obligatorio.',
            'lastname.required' => 'Los apellidos son obligatorios.',
            'identification.required' => 'El documento de identidad es obligatorio.',
            'identification_type.required' => 'Selecciona el tipo de documento.',
            'identification_type.in' => 'El tipo de documento no es válido.',
            'cellphone.required' => 'El celular es obligatorio.',
            'cellphone.regex' => 'El celular debe ser un número de Colombia (10 dígitos, empieza por 3).',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico no es válido.',
            'citie.exists' => 'La ciudad seleccionada no es válida.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'terms.accepted' => 'Debes aceptar los términos y condiciones.',
        ];
    }

    public function attributes(): array
    {
        return [
            'firstname' => 'nombre',
            'lastname' => 'apellidos',
            'identification' => 'documento',
            'email' => 'correo electrónico',
            'cellphone' => 'celular',
            'company' => 'empresa',
            'address' => 'dirección',
            'citie' => 'ciudad',
            'password' => 'contraseña',
            'terms' => 'términos y condiciones',
        ];
    }
}
