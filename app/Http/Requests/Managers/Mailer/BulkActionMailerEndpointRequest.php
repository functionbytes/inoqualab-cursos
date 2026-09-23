<?php

namespace App\Http\Requests\Managers\Mailer;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionMailerEndpointRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->input('action') === 'delete' ? 'newsletters.delete' : 'newsletters.update';

        return $this->user()->can($permission);
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:activate,deactivate,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:mailer_endpoints,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'action' => 'acción',
            'ids' => 'endpoints',
        ];
    }
}
