<?php

namespace App\Http\Requests\Managers\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGeneralSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('settings.update');
    }

    public function rules(): array
    {
        return [
            'page_title' => ['required', 'string', 'max:255'],
            'page_email' => ['nullable', 'email', 'max:255'],
            'page_phone' => ['nullable', 'string', 'max:30'],
            'page_cellphone' => ['nullable', 'string', 'max:30'],
            'page_whatsapp' => ['nullable', 'string', 'max:30'],
            'page_address' => ['nullable', 'string', 'max:500'],
            'social_media_facebook' => ['nullable', 'url', 'max:500'],
            'social_media_instagram' => ['nullable', 'url', 'max:500'],
            'social_media_twitter' => ['nullable', 'url', 'max:500'],
            'social_media_youtube' => ['nullable', 'url', 'max:500'],
            'social_media_linkedin' => ['nullable', 'url', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'page_title.required' => 'El título del sitio es obligatorio.',
            'page_email.email' => 'El correo de contacto no tiene un formato válido.',
            'social_media_facebook.url' => 'La URL de Facebook no es válida.',
            'social_media_instagram.url' => 'La URL de Instagram no es válida.',
            'social_media_twitter.url' => 'La URL de Twitter no es válida.',
            'social_media_youtube.url' => 'La URL de YouTube no es válida.',
            'social_media_linkedin.url' => 'La URL de LinkedIn no es válida.',
        ];
    }

    public function attributes(): array
    {
        return [
            'page_title' => 'título del sitio',
            'page_email' => 'correo de contacto',
            'page_phone' => 'teléfono',
            'page_cellphone' => 'celular',
            'page_whatsapp' => 'WhatsApp',
            'page_address' => 'dirección',
        ];
    }
}
