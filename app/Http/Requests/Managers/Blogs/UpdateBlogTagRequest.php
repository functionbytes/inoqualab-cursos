<?php

namespace App\Http\Requests\Managers\Blogs;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBlogTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('blogs.update');
    }

    public function rules(): array
    {
        return [
            'slack' => ['required', 'string', 'exists:blog_tags,slack'],
            'title' => ['required', 'string', 'max:191'],
            'available' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'slack.required' => 'La etiqueta a actualizar es obligatoria.',
            'slack.exists' => 'La etiqueta a actualizar no existe.',
            'title.required' => 'El título es obligatorio.',
            'title.max' => 'El título no puede superar los 191 caracteres.',
            'available.required' => 'El estado es obligatorio.',
            'available.boolean' => 'El estado no es válido.',
        ];
    }

    public function attributes(): array
    {
        return [
            'slack' => 'etiqueta',
            'title' => 'título',
            'available' => 'estado',
        ];
    }
}
