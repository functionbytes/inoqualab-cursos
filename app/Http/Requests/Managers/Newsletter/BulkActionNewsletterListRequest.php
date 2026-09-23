<?php

namespace App\Http\Requests\Managers\Newsletter;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionNewsletterListRequest extends FormRequest
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
            'ids.*' => ['integer', 'exists:newsletter_lists,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'action' => 'acción',
            'ids' => 'listas',
        ];
    }
}
