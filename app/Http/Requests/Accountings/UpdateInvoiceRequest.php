<?php

namespace App\Http\Requests\Accountings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role === 'accounting';
    }

    public function rules(): array
    {
        return [
            'slack' => ['required', 'string', 'exists:invoices,slack'],
            'condition' => ['required', 'integer', 'exists:invoice_condition,id'],
            'methods' => ['required', 'integer', 'exists:invoice_method,id'],
            'payment' => ['required_if:condition,4', 'nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'slack.required' => 'La factura es obligatoria.',
            'slack.exists' => 'La factura no existe.',
            'condition.required' => 'La condición de pago es obligatoria.',
            'condition.exists' => 'La condición de pago no es válida.',
            'methods.required' => 'El método de pago es obligatorio.',
            'methods.exists' => 'El método de pago no es válido.',
            'payment.required_if' => 'La fecha de pago es obligatoria cuando la factura está pagada.',
            'payment.date' => 'La fecha de pago no es válida.',
        ];
    }

    public function attributes(): array
    {
        return [
            'condition' => 'condición de pago',
            'methods' => 'método de pago',
            'payment' => 'fecha de pago',
        ];
    }
}
