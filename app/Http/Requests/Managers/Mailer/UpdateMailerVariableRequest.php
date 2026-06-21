<?php

namespace App\Http\Requests\Managers\Mailer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMailerVariableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('newsletters.update');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'example_value' => ['nullable', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:50'],
            'is_enabled' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede superar los 255 caracteres.',
            'description.max' => 'La descripción no puede superar los 500 caracteres.',
            'example_value.max' => 'El valor de ejemplo no puede superar los 255 caracteres.',
            'category.required' => 'La categoría es obligatoria.',
            'category.max' => 'La categoría no puede superar los 50 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'description' => 'descripción',
            'example_value' => 'valor de ejemplo',
            'category' => 'categoría',
            'is_enabled' => 'estado',
        ];
    }
}
