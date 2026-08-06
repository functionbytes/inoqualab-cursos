<?php

namespace App\Http\Requests\Managers\Enterprises;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEnterpriseRatesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('enterprises.update');
    }

    public function rules(): array
    {
        return [
            'slack' => ['required', 'string', 'exists:enterprises,slack'],
            'courses' => ['required', 'array'],
            'courses.*' => ['numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'slack.required' => 'La empresa es obligatoria.',
            'slack.exists' => 'La empresa indicada no existe.',
            'courses.required' => 'Es necesario ingresar al menos una tarifa.',
            'courses.*.numeric' => 'El precio debe ser un número.',
            'courses.*.min' => 'El precio no puede ser negativo.',
        ];
    }

    public function attributes(): array
    {
        return [
            'slack' => 'empresa',
            'courses' => 'tarifas',
        ];
    }
}
