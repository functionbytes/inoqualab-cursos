<?php

namespace App\Http\Requests\Supports;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Igual que el resto de este controller: sin permiso Spatie explícito,
        // se apoya en el middleware de rol/panel de la ruta.
        return true;
    }

    public function rules(): array
    {
        return [
            'slack' => ['required', 'string', 'exists:contacts,slack'],
            'reviewed' => ['nullable', 'boolean'],
        ];
    }
}
