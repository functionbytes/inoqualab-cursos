<?php

namespace App\Http\Requests\Managers\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAnalyticsSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('settings.update');
    }

    public function rules(): array
    {
        return [
            'google_analytics_property_id' => ['nullable', 'string', 'regex:/^[0-9]+$/'],
            'google_analytics_measurement_id' => ['nullable', 'string', 'regex:/^G-[A-Z0-9]+$/i'],
            'analytics_cache_lifetime' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'microsoft_clarity_id' => ['nullable', 'string', 'max:20'],
            'tiktok_pixel_id' => ['nullable', 'string', 'max:50'],
            'linkedin_insight_tag_id' => ['nullable', 'string', 'regex:/^[0-9]+$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'google_analytics_property_id.regex' => 'El Property ID de Analytics debe contener solo dígitos.',
            'google_analytics_measurement_id.regex' => 'El Measurement ID debe tener el formato G-XXXXXXXXXX.',
            'analytics_cache_lifetime.min' => 'El tiempo de caché debe ser al menos 1 minuto.',
            'analytics_cache_lifetime.max' => 'El tiempo de caché no puede superar 1440 minutos.',
            'linkedin_insight_tag_id.regex' => 'El LinkedIn Insight Tag ID debe contener solo dígitos.',
        ];
    }
}
