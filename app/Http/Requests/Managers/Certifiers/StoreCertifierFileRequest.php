<?php

namespace App\Http\Requests\Managers\Certifiers;

use Illuminate\Foundation\Http\FormRequest;

class StoreCertifierFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'El archivo es obligatorio.',
            'file.image' => 'El archivo debe ser una imagen.',
            'file.mimes' => 'El archivo debe ser de tipo: jpeg, png, jpg o webp.',
            'file.max' => 'El archivo no puede superar los 5 MB.',
        ];
    }

    public function attributes(): array
    {
        return [
            'file' => 'imagen',
        ];
    }
}
