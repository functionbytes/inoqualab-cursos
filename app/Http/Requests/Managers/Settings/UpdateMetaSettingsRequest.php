<?php

namespace App\Http\Requests\Managers\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMetaSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('settings.update');
    }

    public function rules(): array
    {
        return [
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'meta_title.max' => 'El título no puede superar los 255 caracteres.',
            'meta_description.max' => 'La descripción no puede superar los 500 caracteres.',
            'meta_keywords.max' => 'Las palabras clave no pueden superar los 500 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'meta_title' => 'título',
            'meta_description' => 'descripción',
            'meta_keywords' => 'palabras clave',
        ];
    }
}
