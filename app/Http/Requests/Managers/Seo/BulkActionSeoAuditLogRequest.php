<?php

namespace App\Http\Requests\Managers\Seo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkActionSeoAuditLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('seo.delete');
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'string', Rule::in(['delete'])],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:seo_audit_logs,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'action' => 'acción',
            'ids' => 'registros',
        ];
    }
}
