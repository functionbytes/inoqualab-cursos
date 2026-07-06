<?php

namespace App\Http\Requests\Managers\Enterprises;

use Illuminate\Foundation\Http\FormRequest;

class ImportCoursesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'enterprise' => ['required', 'string'],
            'course' => ['required', 'string'],
            'file' => ['required', 'file', 'mimes:xlsx,csv', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Debes seleccionar un archivo.',
            'file.mimes' => 'El archivo debe ser de tipo xlsx o csv.',
            'file.max' => 'El archivo no puede superar los 2 MB.',
        ];
    }

    public function attributes(): array
    {
        return [
            'enterprise' => 'empresa',
            'course' => 'curso',
            'file' => 'archivo',
        ];
    }
}
