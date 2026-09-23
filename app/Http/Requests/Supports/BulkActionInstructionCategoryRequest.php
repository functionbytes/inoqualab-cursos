<?php

namespace App\Http\Requests\Supports;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionInstructionCategoryRequest extends FormRequest
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
            'ids.*' => ['integer', 'exists:instruction_categories,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'action' => 'acción',
            'ids' => 'categorías',
        ];
    }
}
