<?php

namespace App\Http\Requests\Managers\Settings\Trusteds;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionTrustedRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->input('action') === 'delete' ? 'trusteds.delete' : 'trusteds.update';

        return $this->user()->can($permission);
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:publish,hide,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:trusteds,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'action' => 'acción',
            'ids' => 'aliados',
        ];
    }
}
