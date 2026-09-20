<?php

namespace App\Http\Requests\Managers;

use Illuminate\Foundation\Http\FormRequest;

class StoreBundleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('bundles.create');
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0'],
            'available' => ['nullable', 'in:0,1'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:2000'],
            'meta_keywords' => ['nullable', 'string', 'max:1000'],
            'start_date' => ['nullable', 'date'],
            'expire_at' => ['nullable', 'date', 'after:start_date'],
            'courses' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título es obligatorio.',
            'title.max' => 'El título no puede superar los 255 caracteres.',
            'price.required' => 'El precio es obligatorio.',
            'price.numeric' => 'El precio debe ser un número.',
            'price.min' => 'El precio no puede ser negativo.',
            'start_date.date' => 'La fecha de inicio no es válida.',
            'expire_at.date' => 'La fecha final no es válida.',
            'expire_at.after' => 'La fecha final debe ser posterior a la fecha de inicio.',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'título',
            'description' => 'descripción',
            'price' => 'precio',
            'available' => 'estado',
            'meta_title' => 'meta título',
            'meta_description' => 'meta descripción',
            'meta_keywords' => 'meta palabras clave',
            'start_date' => 'fecha de inicio',
            'expire_at' => 'fecha final',
            'courses' => 'cursos',
        ];
    }
}
