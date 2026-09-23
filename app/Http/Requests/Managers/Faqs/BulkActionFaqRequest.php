<?php

namespace App\Http\Requests\Managers\Faqs;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionFaqRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->input('action') === 'delete' ? 'faqs.delete' : 'faqs.update';

        return $this->user()->can($permission);
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:publish,hide,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:faqs,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'action' => 'acción',
            'ids' => 'preguntas',
        ];
    }
}
