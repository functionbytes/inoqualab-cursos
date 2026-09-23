<?php

namespace App\Http\Requests\Managers\Seo;

use Illuminate\Foundation\Http\FormRequest;

class InlineUpdateSeoMetaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('seo.update');
    }

    public function rules(): array
    {
        return [
            'field' => ['required', 'string', 'in:title,description,keywords,robots,canonical_url,target_keyword,og_title,og_description,og_type,twitter_card'],
            'value' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return [
            'field' => 'campo',
            'value' => 'valor',
        ];
    }
}
