<?php

namespace App\Http\Requests\Managers\Instructions;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionInstructionCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->input('action') === 'delete' ? 'instructions.delete' : 'instructions.update';

        return $this->user()->can($permission);
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:publish,hide,delete'],
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
