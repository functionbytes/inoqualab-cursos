<?php

namespace App\Http\Requests\Accountings;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role === 'accounting';
    }

    public function rules(): array
    {
        return [
            'distributor' => ['required', 'integer', 'exists:distributors,id'],
            'condition' => ['required', 'integer', 'exists:invoice_condition,id'],
            'methods' => ['required', 'integer', 'exists:invoice_method,id'],
            'range' => ['required', 'string', function ($attribute, $value, $fail) {
                $parts = explode(' - ', $value);

                if (count($parts) !== 2) {
                    $fail('El rango de fechas no tiene el formato esperado.');

                    return;
                }

                foreach ($parts as $part) {
                    try {
                        Carbon::parse($part);
                    } catch (\Throwable $e) {
                        $fail('El rango de fechas no es válido.');

                        return;
                    }
                }
            }],
        ];
    }

    public function messages(): array
    {
        return [
            'distributor.required' => 'El distribuidor es obligatorio.',
            'distributor.exists' => 'El distribuidor no existe.',
            'condition.required' => 'La condición de pago es obligatoria.',
            'condition.exists' => 'La condición de pago no es válida.',
            'methods.required' => 'El método de pago es obligatorio.',
            'methods.exists' => 'El método de pago no es válido.',
            'range.required' => 'El rango de fechas es obligatorio.',
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
