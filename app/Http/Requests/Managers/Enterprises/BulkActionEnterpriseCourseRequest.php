<?php

namespace App\Http\Requests\Managers\Enterprises;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionEnterpriseCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('enterprises.delete');
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
