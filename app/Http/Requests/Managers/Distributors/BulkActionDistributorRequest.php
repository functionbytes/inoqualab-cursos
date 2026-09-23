<?php

namespace App\Http\Requests\Managers\Distributors;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionDistributorRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->input('action') === 'delete' ? 'distributors.delete' : 'distributors.update';

        return $this->user()->can($permission);
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:publish,hide,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:distributors,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'action' => 'acción',
            'ids' => 'distribuidores',
        ];
    }
}
