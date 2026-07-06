<?php

namespace App\Http\Requests\Managers\Exams;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('exams.update');
    }

    public function rules(): array
    {
        return [
            'question' => ['required', 'string'],
            'a' => ['nullable', 'string'],
            'b' => ['nullable', 'string'],
            'c' => ['nullable', 'string'],
            'd' => ['nullable', 'string'],
            'answer' => ['required', 'string'],
            'available' => ['nullable', 'in:0,1'],
            'topic' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'question.required' => 'La pregunta es obligatoria.',
            'answer.required' => 'La respuesta es obligatoria.',
            'available.in' => 'El estado seleccionado no es válido.',
        ];
    }

    public function attributes(): array
    {
        return [
            'question' => 'pregunta',
            'a' => 'respuesta a',
            'b' => 'respuesta b',
            'c' => 'respuesta c',
            'd' => 'respuesta d',
            'answer' => 'respuesta',
            'available' => 'estado',
        ];
    }
}
