<?php

namespace App\Http\Requests\Managers\Quizs;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('quizzes.update');
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'timer' => ['nullable', 'integer'],
            'mark' => ['nullable', 'integer'],
            'question' => ['nullable', 'integer'],
            'duration' => ['nullable', 'integer'],
            'day' => ['nullable', 'integer'],
            'lesson' => ['nullable', 'exists:course_lessons,id'],
            'available' => ['nullable', 'in:0,1'],
            'type' => ['nullable', 'in:0,1'],
            'slack' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título es obligatorio.',
            'title.max' => 'El título no puede superar los 255 caracteres.',
            'timer.integer' => 'El tiempo debe ser un número entero.',
            'mark.integer' => 'La cantidad de respuestas correctas debe ser un número entero.',
            'question.integer' => 'La cantidad de preguntas debe ser un número entero.',
            'duration.integer' => 'La duración debe ser un número entero.',
            'day.integer' => 'Los días disponibles deben ser un número entero.',
            'lesson.exists' => 'La clase seleccionada no es válida.',
            'available.in' => 'El estado seleccionado no es válido.',
            'type.in' => 'El tipo de cuestionario no es válido.',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'título',
            'description' => 'descripción',
            'timer' => 'tiempo',
            'mark' => 'respuestas correctas',
            'question' => 'preguntas',
            'duration' => 'duración',
            'day' => 'días disponibles',
            'lesson' => 'clase',
            'available' => 'estado',
            'type' => 'tipo de cuestionario',
        ];
    }
}
