<?php

namespace App\Http\Requests\Managers;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('invoices.update');
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:generada,pendiente,pagada,rechazada'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:invoices,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'action' => 'acción',
            'ids' => 'facturas',
        ];
    }
}
