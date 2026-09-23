<?php

namespace App\Http\Requests\Distributors\Enterprises;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionEnterpriseCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Igual que el resto de este controller: sin permiso Spatie explícito,
        // se apoya en el middleware de rol/panel de la ruta + el ownership
        // check (managedEnterprise()) dentro del controller.
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:courses,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'action' => 'acción',
            'ids' => 'cursos',
        ];
    }
}
