<?php

namespace App\Http\Requests\Managers\Mailer;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionMailerComponentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->input('action') === 'delete' ? 'newsletters.delete' : 'newsletters.update';

        return $this->user()->can($permission);
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:enable,disable,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:mailer_layouts,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'action' => 'acción',
            'ids' => 'componentes',
        ];
    }
}
