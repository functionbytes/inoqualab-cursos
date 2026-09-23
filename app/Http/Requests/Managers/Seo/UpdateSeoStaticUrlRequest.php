<?php

namespace App\Http\Requests\Managers\Seo;

use App\Models\Seo\SeoStaticUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSeoStaticUrlRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('seo.update');
    }

    public function rules(): array
    {
        return [
            'url' => ['required', 'url', 'max:500', Rule::unique('seo_static_urls', 'url')->ignore($this->route('seoStaticUrl'))],
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
