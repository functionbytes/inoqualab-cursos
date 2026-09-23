<?php

namespace App\Http\Requests\Supports;

use Illuminate\Foundation\Http\FormRequest;

class BulkDiscardIncomingMailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:discard'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:incoming_mails,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'action' => 'acción',
            'ids' => 'correos',
        ];
    }
}
