<?php

namespace App\Http\Requests\Managers\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePixelSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('settings.update');
    }

    public function rules(): array
    {
        return [
            'fb_pixel_enable' => ['nullable'],
            'fb_pixel' => ['nullable', 'string', 'max:50', 'regex:/^[0-9]*$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'fb_pixel.regex' => 'El ID de Facebook Pixel solo puede contener números.',
            'fb_pixel.max' => 'El ID de Facebook Pixel no puede superar los 50 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'fb_pixel_enable' => 'habilitar pixel',
            'fb_pixel' => 'ID de Facebook Pixel',
        ];
    }
}
