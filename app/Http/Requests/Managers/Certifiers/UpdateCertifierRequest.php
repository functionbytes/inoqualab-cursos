<?php

namespace App\Http\Requests\Managers\Certifiers;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCertifierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('certifiers.update');
    }

    public function rules(): array
    {
        return [
            'slack' => ['required', 'string', 'exists:certifiers,slack'],
            'firstname' => ['required', 'string', 'max:191'],
            'lastname' => ['required', 'string', 'max:191'],
            'identification' => ['nullable', 'string', 'max:45'],
            'profession' => ['nullable', 'string', 'max:191'],
            'description' => ['nullable', 'string'],
            'available' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'slack.required' => 'El certificador es obligatorio.',
            'slack.exists' => 'El certificador indicado no existe.',
            'firstname.required' => 'El nombre es obligatorio.',
            'lastname.required' => 'El apellido es obligatorio.',
            'available.required' => 'El estado es obligatorio.',
            'available.boolean' => 'El estado no es válido.',
        ];
    }

    public function attributes(): array
    {
        return [
            'slack' => 'certificador',
            'firstname' => 'nombre',
            'lastname' => 'apellido',
            'identification' => 'identificación',
            'profession' => 'profesión',
            'description' => 'descripción',
            'available' => 'estado',
        ];
    }
}
