<?php

namespace App\Http\Requests\Managers\Certifiers;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionCertifierRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->input('action') === 'delete' ? 'certifiers.delete' : 'certifiers.update';

        return $this->user()->can($permission);
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:publish,hide,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:certifiers,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'action' => 'acción',
            'ids' => 'capacitadores',
        ];
    }
}
