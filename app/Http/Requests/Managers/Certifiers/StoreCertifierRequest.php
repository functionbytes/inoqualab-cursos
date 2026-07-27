<?php

namespace App\Http\Requests\Managers\Certifiers;

use Illuminate\Foundation\Http\FormRequest;

class StoreCertifierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('certifiers.create');
    }

    public function rules(): array
    {
        return [
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
            'firstname.required' => 'El nombre es obligatorio.',
            'lastname.required' => 'El apellido es obligatorio.',
            'available.required' => 'El estado es obligatorio.',
            'available.boolean' => 'El estado no es válido.',
        ];
    }

    public function attributes(): array
    {
        return [
            'firstname' => 'nombre',
            'lastname' => 'apellido',
            'identification' => 'identificación',
            'profession' => 'profesión',
            'description' => 'descripción',
            'available' => 'estado',
        ];
    }
}
