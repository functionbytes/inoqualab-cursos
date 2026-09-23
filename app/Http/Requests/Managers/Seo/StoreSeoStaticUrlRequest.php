<?php

namespace App\Http\Requests\Managers\Seo;

use App\Models\Seo\SeoStaticUrl;
use Illuminate\Foundation\Http\FormRequest;

class StoreSeoStaticUrlRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('seo.create');
    }

    public function rules(): array
    {
        return [
            'url' => ['required', 'url', 'max:500', 'unique:seo_static_urls,url'],
            'priority' => ['nullable', 'numeric', 'in:'.implode(',', SeoStaticUrl::PRIORITY_OPTIONS)],
            'changefreq' => ['nullable', 'string', 'in:'.implode(',', SeoStaticUrl::CHANGEFREQ_OPTIONS)],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'url' => 'URL',
            'priority' => 'prioridad',
            'changefreq' => 'frecuencia de cambio',
            'notes' => 'notas',
        ];
    }
}
