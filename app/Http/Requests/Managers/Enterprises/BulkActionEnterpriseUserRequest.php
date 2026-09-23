<?php

namespace App\Http\Requests\Managers\Enterprises;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionEnterpriseUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('enterprises.update');
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:activate,deactivate'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:users,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'action' => 'acción',
            'ids' => 'usuarios',
        ];
    }
}
