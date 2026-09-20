<?php

namespace App\Http\Requests\Managers\Enterprises;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEnterpriseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('enterprises.update');
    }

    public function rules(): array
    {
        return [
            'slack' => ['required', 'string', 'exists:enterprises,slack'],
            'title' => ['required', 'string', 'min:3', 'max:250'],
            // Nullable: hay empresas reales existentes sin NIT registrado
            // (datos legados/importados) que deben poder seguir editándose.
            'nit' => ['nullable', 'string', 'min:3', 'max:250'],
            'address' => ['required', 'string', 'min:3', 'max:250'],
            'cellphone' => ['nullable', 'string', 'regex:/^[0-9]{6,10}$/'],
            'email' => ['required', 'email', 'max:250'],
            'available' => ['required', 'in:0,1'],
        ];
    }

    public function messages(): array
    {
        return [
            'slack.required' => 'La empresa es obligatoria.',
            'slack.exists' => 'La empresa indicada no existe.',
            'title.required' => 'El título es obligatorio.',
            'address.required' => 'La dirección es obligatoria.',
            'cellphone.regex' => 'El celular debe contener solo números (6 a 10 dígitos).',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico no tiene un formato válido.',
            'available.required' => 'El estado es obligatorio.',
            'available.in' => 'El estado seleccionado no es válido.',
        ];
    }

    public function attributes(): array
    {
        return [
            'slack' => 'empresa',
            'title' => 'título',
            'nit' => 'NIT',
            'address' => 'dirección',
            'cellphone' => 'celular',
            'email' => 'correo electrónico',
            'available' => 'estado',
        ];
    }
}
