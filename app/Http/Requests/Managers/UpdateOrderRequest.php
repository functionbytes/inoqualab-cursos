<?php

namespace App\Http\Requests\Managers;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('orders.update');
    }

    public function rules(): array
    {
        return [
            'slack' => ['required', 'string', 'exists:orders,slack'],
            'condition' => ['required', 'integer', 'exists:order_condition,id'],
            'methods' => ['required', 'integer', 'exists:order_method,id'],
            'payment' => ['required_if:condition,4', 'nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'slack.required' => 'La orden es obligatoria.',
            'slack.exists' => 'La orden no existe.',
            'condition.required' => 'La condición de pago es obligatoria.',
            'condition.exists' => 'La condición de pago no es válida.',
            'methods.required' => 'El método de pago es obligatorio.',
            'methods.exists' => 'El método de pago no es válido.',
            'payment.required_if' => 'La fecha de pago es obligatoria cuando la orden está pagada.',
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
