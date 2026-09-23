<?php

namespace App\Http\Requests\Managers\Courses;

use Illuminate\Foundation\Http\FormRequest;

class UpdateChapterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('courses.update');
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:191'],
            'description' => ['nullable', 'string', 'max:191'],
            'position' => ['nullable', 'integer', 'min:0'],
            // course_chapters.available es NOT NULL en BD: dejarlo 'nullable'
            // aquí permitía un submit sin este campo y reventaba el UPDATE con
            // un 500 en vez de un 422 legible (mismo patrón ya visto en Quiz).
            'available' => ['required', 'in:0,1'],
            'slack' => ['required', 'string', 'exists:course_chapters,slack'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título es obligatorio.',
            'title.max' => 'El título no puede superar los 191 caracteres.',
            'description.max' => 'La descripción no puede superar los 191 caracteres.',
            'position.integer' => 'La posición debe ser un número entero.',
            'available.required' => 'Selecciona un estado.',
            'available.in' => 'El estado seleccionado no es válido.',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'título',
            'description' => 'descripción',
            'position' => 'posición',
            'available' => 'estado',
        ];
    }
}
