<?php

namespace App\Http\Requests\Managers\Seo;

use Illuminate\Foundation\Http\FormRequest;

class BulkApplySeoSchemaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('seo.update');
    }

    public function rules(): array
    {
        return [
            'model_type' => ['required', 'string', 'max:200'],
            'schema_type' => ['required', 'string', 'in:Article,Product,FAQPage,Event,HowTo,WebPage,Course'],
            'force' => ['sometimes', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'model_type' => 'tipo de modelo',
            'schema_type' => 'tipo de schema',
            'force' => 'forzar',
        ];
    }
}
