<?php

namespace App\Http\Requests\Managers\Quizs;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('quizzes.create');
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'timer' => ['nullable', 'integer'],
            // quiz_topics.per_q_mark es NOT NULL en BD: 'nullable' aquí dejaba
            // pasar un submit sin este campo y reventaba en el INSERT con 500.
            'mark' => ['required', 'integer'],
            // quiz_topics.show_ans y .quiz_again son NOT NULL en BD (con default
            // a nivel de columna, pero Eloquent inserta NULL explícito si el
            // campo falta, lo que igual revienta el INSERT). El JS del modal ya
            // los marca 'required', así que el Form Request debe exigirlo también.
            'question' => ['required', 'integer'],
            'duration' => ['required', 'integer'],
            'day' => ['nullable', 'integer'],
            // quiz_topics.lesson_id es NOT NULL en BD: si se deja 'nullable' aquí,
            // un submit sin clase seleccionada pasa la validación y revienta en
            // el INSERT con un 500 en vez de un 422 legible.
            'lesson' => ['required', 'exists:course_lessons,id'],
            'available' => ['nullable', 'in:0,1'],
            'type' => ['nullable', 'in:0,1'],
            'course' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título es obligatorio.',
            'title.max' => 'El título no puede superar los 255 caracteres.',
            'timer.integer' => 'El tiempo debe ser un número entero.',
            'mark.required' => 'La cantidad de respuestas correctas es obligatoria.',
            'mark.integer' => 'La cantidad de respuestas correctas debe ser un número entero.',
            'question.required' => 'La cantidad de preguntas es obligatoria.',
            'question.integer' => 'La cantidad de preguntas debe ser un número entero.',
            'duration.required' => 'Selecciona una opción de duración.',
            'duration.integer' => 'La duración debe ser un número entero.',
            'day.integer' => 'Los días disponibles deben ser un número entero.',
            'lesson.required' => 'Selecciona una clase.',
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
