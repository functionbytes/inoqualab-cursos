<?php

namespace App\Http\Requests\Managers\Seo;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionSeoStaticUrlRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('seo.delete');
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'string', 'in:delete,activate,deactivate'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:seo_static_urls,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'action' => 'acción',
            'ids' => 'URLs estáticas',
        ];
    }
}
