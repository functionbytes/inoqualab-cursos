<?php

namespace App\Http\Requests\Managers\Blogs;

use Illuminate\Foundation\Http\FormRequest;

class StoreBlogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('blogs.create');
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:400'],
            'categorie' => ['required', 'exists:blog_categories,id'],
            'available' => ['required', 'boolean'],
            'date' => ['required', 'date'],
            'contents' => ['required', 'string'],
            'description' => ['required', 'string'],
            'tags' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título es obligatorio.',
            'title.max' => 'El título no puede superar los 400 caracteres.',
            'categorie.required' => 'La categoría es obligatoria.',
            'categorie.exists' => 'La categoría seleccionada no existe.',
            'available.required' => 'El estado es obligatorio.',
            'available.boolean' => 'El estado no es válido.',
            'date.required' => 'La fecha es obligatoria.',
            'date.date' => 'La fecha no es válida.',
            'contents.required' => 'El contenido es obligatorio.',
            'description.required' => 'La descripción es obligatoria.',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'título',
            'categorie' => 'categoría',
            'available' => 'estado',
            'date' => 'fecha',
            'contents' => 'contenido',
            'description' => 'descripción',
            'tags' => 'etiquetas',
        ];
    }
}
