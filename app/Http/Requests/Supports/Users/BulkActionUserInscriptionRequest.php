<?php

namespace App\Http\Requests\Supports\Users;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionUserInscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:inscriptions,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'action' => 'acción',
            'ids' => 'inscripciones',
        ];
    }
}
