<?php

namespace App\Http\Requests\Managers\Faqs;

use Illuminate\Foundation\Http\FormRequest;

/** Compartido con el panel de soporte (mismo permiso `faqs.*`). */
class UpdateFaqCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('faqs.update');
    }

    public function rules(): array
    {
        return [
            'slack' => ['required', 'string', 'exists:faq_categories,slack'],
            'title' => ['required', 'string', 'max:191'],
            'available' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título es obligatorio.',
            'title.max' => 'El título no puede superar los 191 caracteres.',
            'available.required' => 'El estado es obligatorio.',
            'available.boolean' => 'El estado no es válido.',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'título',
            'available' => 'estado',
        ];
    }
}
