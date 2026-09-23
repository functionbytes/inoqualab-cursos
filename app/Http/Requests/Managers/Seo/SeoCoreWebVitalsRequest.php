<?php

namespace App\Http\Requests\Managers\Seo;

use Illuminate\Foundation\Http\FormRequest;

class SeoCoreWebVitalsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('seo.update');
    }

    public function rules(): array
    {
        return [
            'url' => ['required', 'url', 'max:500'],
            'strategy' => ['nullable', 'in:mobile,desktop'],
        ];
    }

    public function attributes(): array
    {
        return [
            'url' => 'URL',
            'strategy' => 'estrategia',
        ];
    }
}
