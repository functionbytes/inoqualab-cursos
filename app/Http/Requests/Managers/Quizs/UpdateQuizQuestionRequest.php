<?php

namespace App\Http\Requests\Managers\Quizs;

use App\Http\Requests\Managers\Concerns\ValidatesAssessmentAnswer;
use App\Models\Quiz\QuizQuestion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateQuizQuestionRequest extends FormRequest
{
    use ValidatesAssessmentAnswer;

    public function authorize(): bool
    {
        return $this->user()->can('quizzes.update');
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
            'slack' => ['required', 'string', 'exists:quiz_questions,slack'],
        ];
    }

    /**
     * `answer` guarda una clave (no el texto de la opción), y las claves
     * válidas dependen del tipo de evaluación del topic: 'a'..'d' para
     * selección múltiple, 'true'/'false' para Falso-Verdadero. Sin esto, un
     * `answer` fuera de las opciones reales deja la pregunta sin ninguna
     * respuesta correcta posible para el alumno.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $question = QuizQuestion::where('slack', $this->input('slack'))->first();
            $topic = $question?->topic;
            if (! $topic) {
                return;
            }

            $this->validateAssessmentAnswer($validator, $topic->type);
        });
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
