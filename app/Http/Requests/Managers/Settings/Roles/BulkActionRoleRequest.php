<?php

namespace App\Http\Requests\Managers\Settings\Roles;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('roles.delete');
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:roles,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'action' => 'acción',
            'ids' => 'roles',
        ];
    }
}
