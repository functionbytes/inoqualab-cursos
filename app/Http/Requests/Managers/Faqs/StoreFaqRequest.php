<?php

namespace App\Http\Requests\Managers\Faqs;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Compartido con el panel de soporte: ambos dominios escriben la misma entidad
 * y comprueban el mismo permiso (`faqs.*`), así que duplicar el Form Request
 * solo abriría la puerta a que las dos copias se desincronicen.
 */
class StoreFaqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('faqs.create');
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:400'],
            'description' => ['nullable', 'string'],
            'available' => ['required', 'boolean'],
            'categorie' => ['required', 'integer', 'exists:faq_categories,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título es obligatorio.',
            'title.max' => 'El título no puede superar los 400 caracteres.',
            'available.required' => 'El estado es obligatorio.',
            'available.boolean' => 'El estado no es válido.',
            'categorie.required' => 'La categoría es obligatoria.',
            'categorie.exists' => 'La categoría seleccionada no existe.',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'título',
            'description' => 'descripción',
            'available' => 'estado',
            'categorie' => 'categoría',
        ];
    }
}
