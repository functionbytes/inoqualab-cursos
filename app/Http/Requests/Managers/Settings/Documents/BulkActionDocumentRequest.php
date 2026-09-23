<?php

namespace App\Http\Requests\Managers\Settings\Documents;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->input('action') === 'delete' ? 'documents.delete' : 'documents.update';

        return $this->user()->can($permission);
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:publish,hide,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:documents,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'action' => 'acción',
            'ids' => 'documentos',
        ];
    }
}
