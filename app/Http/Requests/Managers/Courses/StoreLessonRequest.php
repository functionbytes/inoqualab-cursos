<?php

namespace App\Http\Requests\Managers\Courses;

use Illuminate\Foundation\Http\FormRequest;

class StoreLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'chapter' => ['required'],
            'type' => ['required', 'in:1,2,3,4,5,6'],
            'position' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título es obligatorio.',
            'chapter.required' => 'El tema es obligatorio.',
            'type.required' => 'El tipo de lección es obligatorio.',
            'type.in' => 'El tipo de lección no es válido.',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'título',
            'chapter' => 'tema',
            'type' => 'tipo de lección',
            'position' => 'posición',
        ];
    }
}
