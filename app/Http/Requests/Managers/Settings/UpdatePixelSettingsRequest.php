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
            'meta_pixel_enable' => ['nullable'],
            'meta_pixel_id' => ['nullable', 'string', 'regex:/^[0-9]+$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'meta_pixel_id.regex' => 'El ID de Meta Pixel solo puede contener números.',
        ];
    }

    public function attributes(): array
    {
        return [
            'meta_pixel_enable' => 'habilitar pixel',
            'meta_pixel_id' => 'ID de Meta Pixel',
        ];
    }
}
