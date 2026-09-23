<?php

namespace App\Http\Requests\Managers\Seo;

use Illuminate\Foundation\Http\FormRequest;

class BulkGenerateSeoOrphanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('seo.update');
    }

    public function rules(): array
    {
        return [
            'items' => ['nullable', 'array'],
            'items.*.model_class' => ['required_with:items', 'string'],
            'items.*.model_id' => ['required_with:items', 'integer'],
        ];
    }

    public function attributes(): array
    {
        return [
            'items' => 'registros',
        ];
    }
}
