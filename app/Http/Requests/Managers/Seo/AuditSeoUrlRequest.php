<?php

namespace App\Http\Requests\Managers\Seo;

use Illuminate\Foundation\Http\FormRequest;

class AuditSeoUrlRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('seo.update');
    }

    public function rules(): array
    {
        return [
            'url' => ['required', 'url'],
        ];
    }

    public function attributes(): array
    {
        return [
            'url' => 'URL',
        ];
    }
}
