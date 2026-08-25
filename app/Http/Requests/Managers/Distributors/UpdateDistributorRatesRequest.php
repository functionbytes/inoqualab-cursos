<?php

namespace App\Http\Requests\Managers\Distributors;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDistributorRatesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('distributors.update');
    }

    public function rules(): array
    {
        return [
            'slack' => ['required', 'string', 'exists:distributors,slack'],
            'courses' => ['required', 'array'],
            'courses.*' => ['numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'slack.required' => 'El distribuidor es obligatorio.',
            'slack.exists' => 'El distribuidor indicado no existe.',
            'courses.required' => 'Es necesario ingresar al menos una tarifa.',
            'courses.*.numeric' => 'El precio debe ser un número.',
            'courses.*.min' => 'El precio no puede ser negativo.',
        ];
    }

    public function attributes(): array
    {
        return [
            'slack' => 'distribuidor',
            'courses' => 'tarifas',
        ];
    }
}
