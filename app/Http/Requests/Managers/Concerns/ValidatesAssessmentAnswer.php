<?php

namespace App\Http\Requests\Managers\Concerns;

use Illuminate\Validation\Validator;

/**
 * Validación compartida entre las preguntas de Quiz y Exam (topics tipo 0 =
 * Falso/Verdadero, tipo 1 = selección múltiple). `answer` NO es una única
 * letra: para selección múltiple guarda una lista de letras 'a'..'d'
 * separadas por coma sin espacios (ej. "a,d", "b,c,d") -- confirmado contra
 * datos reales, donde más de la mitad de las preguntas de selección múltiple
 * tienen más de una opción correcta. Para Falso/Verdadero, los datos reales
 * usan 'TRUE'/'FALSE' en mayúsculas, así que la comparación es
 * case-insensitive.
 */
trait ValidatesAssessmentAnswer
{
    private const MULTIPLE_CHOICE_OPTIONS = ['a', 'b', 'c', 'd'];

    /**
     * @param  Validator  $validator
     */
    protected function validateAssessmentAnswer($validator, ?int $topicType): void
    {
        $answer = (string) $this->input('answer');

        if ($topicType == 1) {
            $letters = $answer === '' ? [] : explode(',', strtolower($answer));
            $unknown = array_diff($letters, self::MULTIPLE_CHOICE_OPTIONS);
            $hasDuplicates = count($letters) !== count(array_unique($letters));

            if (empty($letters) || ! empty($unknown) || $hasDuplicates) {
                $validator->errors()->add('answer', 'La respuesta seleccionada no es válida para este tipo de pregunta.');
            }

            foreach (self::MULTIPLE_CHOICE_OPTIONS as $option) {
                if (blank($this->input($option))) {
                    $validator->errors()->add($option, 'Completa las 4 opciones de respuesta.');
                }
            }

            return;
        }

        if (! in_array(strtolower($answer), ['true', 'false'], true)) {
            $validator->errors()->add('answer', 'La respuesta seleccionada no es válida para este tipo de pregunta.');
        }
    }
}
