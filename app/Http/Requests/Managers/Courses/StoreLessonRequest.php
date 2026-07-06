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
            // exists (no 'in:' hardcodeado): course_types tiene 7 tipos reales
            // (incluye TEXTO=7); un 'in:1..6' rechazaría ese tipo válido.
            'type' => ['required', 'exists:course_types,id'],
            'position' => ['nullable', 'integer', 'min:0'],
            'file' => ['nullable', 'file', 'max:512000'],
            'url' => ['nullable', 'string', 'max:2048'],
            'size' => ['nullable'],
            'duration' => ['nullable', 'string', 'max:255'],
            'platform' => ['nullable', 'string', 'max:255'],
            'detail' => ['nullable', 'string'],
            'available' => ['nullable'],
            'course' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título es obligatorio.',
            'title.max' => 'El título no puede superar los 255 caracteres.',
            'chapter.required' => 'El tema es obligatorio.',
            'type.required' => 'El tipo de lección es obligatorio.',
            'type.in' => 'El tipo de lección no es válido.',
            'file.file' => 'El archivo adjunto no es válido.',
            'file.max' => 'El archivo no puede superar los 500 MB.',
            'url.max' => 'El enlace no puede superar los 2048 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'título',
            'chapter' => 'tema',
            'type' => 'tipo de lección',
            'position' => 'posición',
            'file' => 'archivo',
            'url' => 'enlace',
            'size' => 'tamaño',
            'duration' => 'duración',
            'platform' => 'plataforma',
            'detail' => 'detalle',
            'available' => 'estado',
        ];
    }
}
