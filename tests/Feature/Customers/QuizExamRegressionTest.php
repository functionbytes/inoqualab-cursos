<?php

namespace Tests\Feature\Customers;

use App\Http\Controllers\Customers\Concerns\ResolvesInscription;
use App\Models\Course\Course;
use App\Models\Inscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Exceptions\HttpResponseException;
use Tests\TestCase;

/**
 * Regresión de la lógica de quiz/examen del cliente que introdujo el commit
 * 4b49d5e ("Corregir bugs en navegación de cursos y lógica de quiz/examen").
 * Cubre: cálculo del puntaje mínimo de aprobación, validación de propiedad de
 * la inscripción (anti-IDOR) y el guard server-side del examen final.
 */
class QuizExamRegressionTest extends TestCase
{
    use RefreshDatabase;

    /** Harness que expone los métodos protegidos del trait. */
    private function harness(): object
    {
        return new class
        {
            use ResolvesInscription;

            public function passing($exam): float
            {
                return $this->passingScoreFor($exam);
            }

            public function resolve($user, $courseId = null): Inscription
            {
                return $this->resolveInscription($user, $courseId);
            }

            public function examAccess($course, $inscription): void
            {
                $this->assertExamAccessible($course, $inscription);
            }
        };
    }

    // ── passingScoreFor: puntaje mínimo derivado del topic ────────────────

    public function test_passing_score_is_100_when_topic_missing_or_unset(): void
    {
        $h = $this->harness();

        $this->assertSame(100.0, $h->passing((object) ['topic' => null]));
        $this->assertSame(100.0, $h->passing((object) ['topic' => (object) ['show_ans' => 0, 'per_q_mark' => 5]]));
    }

    public function test_passing_score_is_derived_from_topic_ratio(): void
    {
        $h = $this->harness();

        // per_q_mark / show_ans * 100
        $this->assertSame(80.0, $h->passing((object) ['topic' => (object) ['show_ans' => 10, 'per_q_mark' => 8]]));
        $this->assertSame(75.0, $h->passing((object) ['topic' => (object) ['show_ans' => 4, 'per_q_mark' => 3]]));
        $this->assertSame(50.0, $h->passing((object) ['topic' => (object) ['show_ans' => 2, 'per_q_mark' => 1]]));
    }

    // ── resolveInscription: propiedad por user_id (anti-IDOR) ─────────────

    public function test_resolve_inscription_returns_owned_inscription(): void
    {
        $user = User::factory()->customer()->create();
        $inscription = Inscription::factory()->create(['user_id' => $user->id]);

        $resolved = $this->harness()->resolve($user, $inscription->course_id);

        $this->assertTrue($resolved->is($inscription));
    }

    public function test_resolve_inscription_redirects_when_user_has_no_inscription(): void
    {
        $owner = User::factory()->customer()->create();
        $intruder = User::factory()->customer()->create();
        $inscription = Inscription::factory()->create(['user_id' => $owner->id]);

        // Otro usuario intentando resolver la inscripción de un curso ajeno
        // no recibe la inscripción del dueño: lanza redirect (no IDOR).
        $this->expectException(HttpResponseException::class);
        $this->harness()->resolve($intruder, $inscription->course_id);
    }

    // ── assertExamAccessible: guard del examen final ──────────────────────

    public function test_exam_is_accessible_when_course_has_no_lessons(): void
    {
        $course = Course::factory()->create();
        $inscription = Inscription::factory()->create(['course_id' => $course->id]);

        // 0 lecciones => no hay nada que culminar => accesible (no lanza).
        $this->harness()->examAccess($course, $inscription);

        $this->assertTrue(true); // llegó aquí sin excepción
    }
}
