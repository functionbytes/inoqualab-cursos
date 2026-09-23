<?php

namespace App\Http\Requests\Managers\Departments;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->input('action') === 'delete' ? 'departments.delete' : 'departments.update';

        return $this->user()->can($permission);
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:activate,deactivate,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:departments,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'action' => 'acción',
            'ids' => 'departamentos',
        ];
    }
}
