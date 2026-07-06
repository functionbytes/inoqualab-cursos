<?php

namespace App\Http\Requests\Managers\Settings\Trusteds;

use Illuminate\Foundation\Http\FormRequest;

class StoreTrustedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('trusteds.create');
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'available' => ['nullable', 'in:0,1'],
            'url' => ['nullable', 'string', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título es obligatorio.',
            'title.max' => 'El título no puede superar los 255 caracteres.',
            'available.in' => 'El estado no es válido.',
            'url.max' => 'El enlace no puede superar los 2048 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'título',
            'available' => 'estado',
            'url' => 'enlace',
        ];
    }
}
