<?php

namespace App\Http\Requests\Managers;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('invoices.create');
    }

    public function rules(): array
    {
        return [
            // El distribuidor se resuelve por slack via Distributor::slack() en el
            // controller, que ya responde 404 (no 422) si no existe -- no duplicar
            // aqui con `exists:` para no romper ese contrato de respuesta.
            'distributor' => ['required', 'string'],
            'condition' => ['required', 'integer', 'exists:invoice_condition,id'],
            'methods' => ['required', 'integer', 'exists:invoice_method,id'],
            // El formato "fecha_inicio - fecha_fin" se valida en el controller
            // (mensaje de negocio propio); aqui solo garantizamos el tipo.
            'range' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'distributor.required' => 'El distribuidor es obligatorio.',
            'condition.required' => 'La condición de pago es obligatoria.',
            'condition.exists' => 'La condición de pago no es válida.',
            'methods.required' => 'El método de pago es obligatorio.',
            'methods.exists' => 'El método de pago no es válido.',
        ];
    }

    public function attributes(): array
    {
        return [
            'distributor' => 'distribuidor',
            'condition' => 'condición de pago',
            'methods' => 'método de pago',
            'range' => 'rango de fechas',
        ];
    }
}
