<?php

namespace App\Http\Requests\Distributors\Enterprises;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionEnterpriseRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Igual que el resto de este controller: sin permiso Spatie explícito,
        // se apoya en el middleware de rol/panel de la ruta + el ownership
        // check (scoping a app('distributor')) dentro del controller.
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:publish,hide,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:enterprises,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'action' => 'acción',
            'ids' => 'empresas',
        ];
    }
}
