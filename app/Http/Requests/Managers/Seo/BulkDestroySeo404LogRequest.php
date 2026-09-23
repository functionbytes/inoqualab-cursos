<?php

namespace App\Http\Requests\Managers\Seo;

use Illuminate\Foundation\Http\FormRequest;

class BulkDestroySeo404LogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('seo.delete');
    }

    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:seo_404_logs,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'ids' => 'registros',
        ];
    }
}
