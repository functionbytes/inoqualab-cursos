<?php

namespace App\Http\Requests\Managers\Seo;

use Illuminate\Foundation\Http\FormRequest;

class GenerateSeoOrphanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('seo.update');
    }

    public function rules(): array
    {
        return [
            'model_class' => ['required', 'string'],
            'model_id' => ['required', 'integer'],
        ];
    }

    public function attributes(): array
    {
        return [
            'model_class' => 'tipo de modelo',
            'model_id' => 'registro',
        ];
    }
}
