<?php

namespace Tests\Feature\Customers;

use App\Models\Course\Course;
use App\Models\Course\CourseChapter;
use App\Models\Course\CourseLesson;
use App\Models\Course\CourseType;
use App\Models\Inscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Regresión de CoursesController::createOrUpdateProgress tras cambiar de
 * "SELECT + create()" (no atómico) a createOrFirst dentro de una transacción
 * — el propio patrón que dejó 413k filas duplicadas en course_progress antes
 * de que existiera el índice único (inscription_id, lesson_id).
 */
class LessonRealizedIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_marking_the_same_lesson_twice_does_not_duplicate_progress_or_percent(): void
    {
        $customer = User::factory()->role('customer')->create();
        $course = Course::factory()->create();
        $type = CourseType::create(['slack' => Str::random(10), 'title' => 'Clase', 'slug' => 'lesson']);
        $chapter = CourseChapter::factory()->create(['course_id' => $course->id]);
        $lesson = CourseLesson::create([
            'slack' => Str::random(10),
            'course_id' => $course->id,
            'chapter_id' => $chapter->id,
            'type_id' => $type->id,
            'title' => 'Única lección',
            'position' => 1,
            'available' => 1,
        ]);

        $inscription = Inscription::factory()->create([
            'user_id' => $customer->id,
            'course_id' => $course->id,
        ]);

        $marcar = fn () => $this->actingAs($customer)
            ->post(route('customers.courses.realized'), ['lesson' => $lesson->id]);

        $marcar()->assertRedirect();
        $marcar()->assertRedirect(); // reintento / doble clic sobre la misma lección

        $this->assertSame(
            1,
            DB::table('course_progress')
                ->where('inscription_id', $inscription->id)
                ->where('lesson_id', $lesson->id)
                ->count(),
            'La segunda marca no debe duplicar la fila de progreso.'
        );

        // Única lección del curso, culminada: 100%. Si el segundo POST volviera
        // a sumar, el bug original habría dejado un porcentaje por encima de 100.
        $this->assertSame(100.0, (float) $inscription->fresh()->percent);
    }
}
