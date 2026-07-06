<?php

namespace App\Http\Requests\Managers\Distributors;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDistributorEnterprisesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * El select multiple envía las empresas como string separado por comas
     * (`"1,4,9"`); se normaliza a array antes de validar cada id.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'enterprises' => $this->filled('enterprises') ? explode(',', (string) $this->input('enterprises')) : [],
        ]);
    }

    public function rules(): array
    {
        return [
            'slack' => ['required', 'string', 'exists:distributors,slack'],
            'enterprises' => ['array'],
            'enterprises.*' => ['integer', 'exists:enterprises,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'slack.required' => 'El distribuidor es obligatorio.',
            'slack.exists' => 'El distribuidor indicado no existe.',
            'enterprises.*.integer' => 'El identificador de la empresa no es válido.',
            'enterprises.*.exists' => 'Una de las empresas seleccionadas no existe.',
        ];
    }

    public function attributes(): array
    {
        return [
            'slack' => 'distribuidor',
            'enterprises' => 'empresas',
        ];
    }
}
