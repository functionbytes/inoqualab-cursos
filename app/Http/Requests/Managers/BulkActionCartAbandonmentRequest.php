<?php

namespace App\Http\Requests\Managers;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionCartAbandonmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // El resto del controller (index()) tampoco valida permisos Spatie
        // explícitos -- se apoya en el middleware de rol/panel de la ruta.
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:delete,remind'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:cart_abandonments,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'action' => 'acción',
            'ids' => 'registros',
        ];
    }
}
