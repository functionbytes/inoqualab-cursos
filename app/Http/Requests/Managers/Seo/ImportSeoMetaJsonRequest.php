<?php

namespace App\Http\Requests\Managers\Seo;

use Illuminate\Foundation\Http\FormRequest;

class ImportSeoMetaJsonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('seo.update');
    }

    public function rules(): array
    {
        return [
            'json_file' => ['required', 'file', 'mimes:json,txt', 'max:51200'],
            'skip_existing' => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'json_file' => 'archivo JSON',
            'skip_existing' => 'omitir existentes',
        ];
    }
}
