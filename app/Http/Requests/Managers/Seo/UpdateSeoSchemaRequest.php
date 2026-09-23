<?php

namespace App\Http\Requests\Managers\Seo;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSeoSchemaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('seo.update');
    }

    public function rules(): array
    {
        return [
            'schema_type' => ['nullable', 'string', 'in:Article,Product,FAQPage,Event,HowTo,WebPage,Course'],
            'schema_custom' => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'schema_type' => 'tipo de schema',
            'schema_custom' => 'schema personalizado',
        ];
    }
}
