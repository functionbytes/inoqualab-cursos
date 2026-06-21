<?php

namespace App\Http\Requests\Managers\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSeoSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('settings.update');
    }

    public function rules(): array
    {
        return [
            'seo_title_suffix' => ['nullable', 'string', 'max:100'],
            'seo_site_name' => ['nullable', 'string', 'max:255'],
            'seo_og_image_default' => ['nullable', 'url'],
            'seo_twitter_site' => ['nullable', 'string', 'max:50'],
            'seo_google_verification' => ['nullable', 'string', 'max:100'],
            'seo_bing_verification' => ['nullable', 'string', 'max:100'],
            'seo_pinterest_verification' => ['nullable', 'string', 'max:100'],
            'seo_baidu_verification' => ['nullable', 'string', 'max:100'],
            'seo_yandex_verification' => ['nullable', 'string', 'max:100'],
            'seo_indexnow_enabled' => ['nullable', 'string', 'max:1'],
            'seo_indexnow_key' => ['nullable', 'string', 'max:100'],
            'robots_txt' => ['nullable', 'string'],
            'llms_txt' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'seo_og_image_default.url' => 'La URL de la imagen OG por defecto no es válida.',
            'seo_title_suffix.max' => 'El sufijo del título no puede superar 100 caracteres.',
            'seo_site_name.max' => 'El nombre del sitio no puede superar 255 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'seo_title_suffix' => 'sufijo del título',
            'seo_site_name' => 'nombre del sitio',
            'seo_og_image_default' => 'imagen OG por defecto',
            'seo_twitter_site' => 'cuenta de Twitter',
        ];
    }
}
