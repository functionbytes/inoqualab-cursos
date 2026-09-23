<?php

namespace App\Http\Requests\Managers\Courses;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('courses.create');
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'available' => ['nullable', 'in:0,1'],
            'course' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título es obligatorio.',
            'title.max' => 'El título no puede superar los 255 caracteres.',
            'available.in' => 'El estado seleccionado no es válido.',
            'course.required' => 'El curso es obligatorio.',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'título',
            'description' => 'descripción',
            'available' => 'estado',
            'course' => 'curso',
        ];
    }
}
