<?php

namespace App\Http\Requests\Managers\Courses;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('courses.update');
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'duration' => ['nullable', 'integer', 'min:0'],
            'day' => ['nullable', 'integer', 'min:0'],
            'categorie' => ['required'],
            'level' => ['nullable', 'in:Principiante,Intermedio,Avanzado'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título del curso es obligatorio.',
            'price.numeric' => 'El precio debe ser un número.',
            'price.min' => 'El precio no puede ser negativo.',
            'discount.numeric' => 'El descuento debe ser un número.',
            'discount.min' => 'El descuento no puede ser negativo.',
            'duration.integer' => 'La duración debe ser un número entero.',
            'categorie.required' => 'La categoría es obligatoria.',
            'level.in' => 'El nivel seleccionado no es válido.',
            'rating.numeric' => 'La calificación debe ser un número.',
            'rating.min' => 'La calificación no puede ser menor que 0.',
            'rating.max' => 'La calificación no puede ser mayor que 5.',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'título',
            'price' => 'precio',
            'discount' => 'descuento',
            'duration' => 'duración',
            'day' => 'días',
            'categorie' => 'categoría',
            'level' => 'nivel',
            'rating' => 'calificación',
        ];
    }
}
