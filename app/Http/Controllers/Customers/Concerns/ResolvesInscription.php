<?php

namespace App\Http\Controllers\Customers\Concerns;

use App\Models\Course\CourseProgress;
use App\Models\Inscription;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Cache;

trait ResolvesInscription
{
    /**
     * Calcula el puntaje mínimo de aprobación (%) para un Exam dado,
     * derivándolo de la configuración del topic (per_q_mark / show_ans).
     */
    protected function passingScoreFor($exam): float
    {
        $topic = $exam?->topic;

        return ($topic && $topic->show_ans > 0)
            ? round(($topic->per_q_mark / $topic->show_ans) * 100, 2)
            : 100;
    }

    /**
     * Recupera la inscripción del usuario para un curso.
     * Usa cache (tolerante a fallos de Redis) y valida siempre la propiedad por user_id.
     * Si no existe inscripción, redirige a la lista de cursos.
     */
    protected function resolveInscription($user, $courseId = null): Inscription
    {
        $slack = rescue(fn () => Cache::get('inscription'.$user->slack), null, false);

        $inscription = $slack
            ? Inscription::where('slack', $slack)->where('user_id', $user->id)->first()
            : null;

        if (! $inscription && $courseId) {
            $inscription = Inscription::where('user_id', $user->id)
                ->where('course_id', $courseId)
                ->first();

            if ($inscription) {
                rescue(fn () => Cache::put('inscription'.$user->slack, $inscription->slack, 6000), null, false);
            }
        }

        if (! $inscription) {
            throw new HttpResponseException(redirect()->route('customers.courses'));
        }

        return $inscription;
    }

    /**
     * Verifica server-side que el usuario puede acceder a una lección/quiz.
     * Espeja la regla del sidebar: se permite si la lección ya está culminada
     * o si su lección inmediatamente anterior (orden del curso) está culminada
     * (o no existe, es decir, es la primera). Evita saltarse el orden por URL.
     */
    protected function assertLessonAccessible($lesson, $inscription, $userId): void
    {
        // Lección ya culminada → accesible
        if (CourseProgress::validate($lesson->id, $inscription->id, $userId)) {
            return;
        }

        $previous = CourseProgress::prevNext($lesson->id, 'prev');

        // No hay anterior (primera lección del curso) → accesible
        if ($previous === 'true' || ! $previous) {
            return;
        }

        // La anterior está culminada → es la "siguiente" permitida
        if (CourseProgress::validate($previous->id, $inscription->id, $userId)) {
            return;
        }

        throw new HttpResponseException(
            redirect()->route('customers.courses.content', $inscription->slack)
        );
    }

    /**
     * Verifica server-side que el examen final está habilitado:
     * todas las lecciones del curso deben estar culminadas.
     */
    protected function assertExamAccessible($course, $inscription): void
    {
        $totalLessons = $course->lessons()->count();
        $completed = $inscription->progress()->count();

        if ($totalLessons > 0 && $completed < $totalLessons) {
            throw new HttpResponseException(
                redirect()->route('customers.courses.content', $inscription->slack)
            );
        }
    }
}
