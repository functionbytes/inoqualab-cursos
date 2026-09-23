<?php

namespace App\Http\Requests\Supports;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInstructionCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'slack' => ['required', 'string', 'exists:instruction_categories,slack'],
            'title' => ['required', 'string', 'max:191'],
            'available' => ['required', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'título',
            'available' => 'estado',
        ];
    }
}
