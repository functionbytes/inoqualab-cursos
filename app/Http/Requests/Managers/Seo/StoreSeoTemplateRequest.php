<?php

namespace App\Http\Requests\Managers\Seo;

use Illuminate\Foundation\Http\FormRequest;

class StoreSeoTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('seo.create');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'model_type' => ['nullable', 'string', 'max:255'],
            'title_pattern' => ['nullable', 'string', 'max:200'],
            'description_pattern' => ['nullable', 'string', 'max:500'],
            'og_type' => ['nullable', 'string', 'max:50'],
            'twitter_card' => ['nullable', 'string', 'max:50'],
            'robots' => ['nullable', 'string', 'max:100'],
            'is_active' => ['boolean'],
            'priority' => ['integer', 'min:0', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'model_type' => 'tipo de modelo',
            'title_pattern' => 'patrón de título',
            'description_pattern' => 'patrón de descripción',
            'og_type' => 'tipo Open Graph',
            'twitter_card' => 'tarjeta Twitter',
            'robots' => 'robots',
            'priority' => 'prioridad',
        ];
    }
}
