<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Collection;

/**
 * Lógica compartida entre los controladores de exámenes y quizzes (topics y
 * preguntas), que son casi idénticos: opciones de respuesta según el tipo,
 * tipos de evaluación para el select, y el seteo de campos de la pregunta.
 */
trait BuildsAssessmentForms
{
    /**
     * Opciones de respuesta según el tipo de pregunta.
     * 0 = Falso/Verdadero, 1 = selección múltiple (A/B/C/D).
     *
     * @return Collection<string,string> id => etiqueta
     */
    protected function assessmentAnswers(?int $type): Collection
    {
        return match ($type) {
            0 => collect(['true' => 'Verdadero', 'false' => 'Falso']),
            1 => collect(['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D']),
            default => collect(),
        };
    }

    /**
     * Tipos de evaluación para el select del topic.
     *
     * @param  bool  $withBlank  Antepone una opción en blanco (para formularios de alta).
     * @return Collection<string,string> id => etiqueta
     */
    protected function assessmentTypes(bool $withBlank = false): Collection
    {
        $types = collect([
            '1' => 'Selección Multiple',
            '0' => 'Falso - Verdadero',
        ]);

        return $withBlank ? collect(['' => ''])->union($types) : $types;
    }

    /**
     * Asigna a la pregunta las opciones a/b/c/d (solo selección múltiple) y la
     * respuesta correcta. Común a store y update de exam/quiz.
     */
    protected function applyAnswerFields(object $question, ?int $type, $request): void
    {
        if ($type == 1) {
            $question->a = $request->a;
            $question->b = $request->b;
            $question->c = $request->c;
            $question->d = $request->d;
        }

        $question->answer = $request->answer;
    }

    /**
     * Normaliza `answer` a minúsculas para precargar el <select> de edición.
     * Las opciones del <select> son siempre minúsculas ('true'/'false',
     * 'a'/'b'/'c'/'d'), pero los datos reales de Falso/Verdadero se guardan
     * en mayúsculas ('TRUE'/'FALSE' -- ver ValidatesAssessmentAnswer). Sin
     * esto, el <select> no encuentra ninguna opción que coincida y queda sin
     * seleccionar al editar, aunque la pregunta sí tenga una respuesta real.
     */
    protected function answerForEdit(?string $answer): ?string
    {
        return $answer === null ? null : strtolower($answer);
    }
}
