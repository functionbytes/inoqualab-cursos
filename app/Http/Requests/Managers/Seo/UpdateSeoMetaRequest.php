<?php

namespace App\Http\Requests\Managers\Seo;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSeoMetaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('seo.update');
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:70'],
            'description' => ['nullable', 'string', 'max:170'],
            'keywords' => ['nullable', 'string', 'max:500'],
            'og_title' => ['nullable', 'string', 'max:95'],
            'og_description' => ['nullable', 'string', 'max:200'],
            'og_image' => ['nullable', 'url'],
            'og_type' => ['nullable', 'in:website,article,product'],
            'twitter_card' => ['nullable', 'in:summary,summary_large_image'],
            'canonical_url' => ['nullable', 'url'],
            'robots' => ['nullable', 'string', 'max:50'],
            'target_keyword' => ['nullable', 'string', 'max:100'],
            'schema_type' => ['nullable', 'string', 'max:50'],
            'schema_custom' => ['nullable', 'json'],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'título',
            'description' => 'descripción',
            'keywords' => 'palabras clave',
            'og_title' => 'título Open Graph',
            'og_description' => 'descripción Open Graph',
            'og_image' => 'imagen Open Graph',
            'og_type' => 'tipo Open Graph',
            'twitter_card' => 'tarjeta Twitter',
            'canonical_url' => 'URL canónica',
            'robots' => 'robots',
            'target_keyword' => 'palabra clave objetivo',
            'schema_type' => 'tipo de schema',
            'schema_custom' => 'schema personalizado',
        ];
    }
}
