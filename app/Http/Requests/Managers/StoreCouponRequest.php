<?php

namespace App\Http\Requests\Managers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        // La ruta del panel (middleware manager + panel.permission) ya autoriza.
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $isPercent = (string) $this->input('type') === '1';

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'code' => ['required', 'string', 'max:50', Rule::unique('coupons', 'code')],
            'type' => ['required', 'in:0,1'],
            'amount' => array_values(array_filter(['required', 'numeric', 'min:0', $isPercent ? 'max:100' : null])),
            'available' => ['nullable', 'in:0,1'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'limit' => ['nullable', 'integer', 'min:0'],
            'bundles' => ['nullable'],
            'courses' => ['nullable'],
            'date_var' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título es obligatorio.',
            'code.required' => 'El código es obligatorio.',
            'code.unique' => 'Ya existe un cupón con ese código.',
            'type.required' => 'El tipo de cupón es obligatorio.',
            'type.in' => 'El tipo de cupón no es válido.',
            'amount.required' => 'El descuento es obligatorio.',
            'amount.numeric' => 'El descuento debe ser un número.',
            'amount.min' => 'El descuento no puede ser negativo.',
            'amount.max' => 'Un cupón de porcentaje no puede superar el 100%.',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'título',
            'code' => 'código',
            'type' => 'tipo',
            'amount' => 'descuento',
            'min_price' => 'importe mínimo',
            'limit' => 'límite de usos',
        ];
    }
}
