<?php

namespace Tests\Feature\Customers;

use App\Models\Course\Course;
use App\Models\Course\CourseChapter;
use App\Models\Course\CourseLesson;
use App\Models\Course\CourseType;
use App\Models\Inscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Regresión: CoursesController::player() (única ruta que reproduce el video
 * real de una lección -- lesion() solo la embebe) verificaba inscripción
 * activa pero NO llamaba a assertLessonAccessible(), el guard que sí aplican
 * lesion() y realized() para impedir saltarse el orden del curso por URL.
 * Un cliente inscrito podía ver el video de CUALQUIER lección (incluida una
 * no desbloqueada) apuntando directo a /content/player/{lesson}.
 */
class CoursePlayerLessonOrderTest extends TestCase
{
    use RefreshDatabase;

    private function makeCourseWithTwoLessons(): array
    {
        $course = Course::factory()->create();
        $type = CourseType::create(['slack' => Str::random(10), 'title' => 'Clase', 'slug' => 'lesson']);
        $chapter = CourseChapter::factory()->create(['course_id' => $course->id]);

        $first = CourseLesson::create([
            'slack' => Str::random(10),
            'course_id' => $course->id,
            'chapter_id' => $chapter->id,
            'type_id' => $type->id,
            'title' => 'Primera lección',
            'position' => 1,
            'available' => 1,
            'url' => 'https://vimeo.com/111111',
        ]);

        $second = CourseLesson::create([
            'slack' => Str::random(10),
            'course_id' => $course->id,
            'chapter_id' => $chapter->id,
            'type_id' => $type->id,
            'title' => 'Segunda lección',
            'position' => 2,
            'available' => 1,
            'url' => 'https://vimeo.com/222222',
        ]);

        return [$course, $first, $second];
    }

    public function test_cannot_play_a_lesson_ahead_of_the_current_progress(): void
    {
        [$course, $first, $second] = $this->makeCourseWithTwoLessons();
        $customer = User::factory()->role('customer')->create();
        Inscription::factory()->create(['user_id' => $customer->id, 'course_id' => $course->id]);

        // Sin haber culminado la primera lección, pedir directo el video de la
        // segunda por URL debía bloquearse -- antes del fix, se servía igual.
        $this->actingAs($customer)
            ->get(route('customers.courses.player', $second->id))
            ->assertRedirect(route('customers.courses.content', Inscription::where('user_id', $customer->id)->first()->slack));
    }

    public function test_can_play_the_first_lesson_of_the_course(): void
    {
        [$course, $first, $second] = $this->makeCourseWithTwoLessons();
        $customer = User::factory()->role('customer')->create();
        Inscription::factory()->create(['user_id' => $customer->id, 'course_id' => $course->id]);

        $this->actingAs($customer)
            ->get(route('customers.courses.player', $first->id))
            ->assertOk();
    }

    public function test_can_play_the_next_lesson_once_the_previous_is_completed(): void
    {
        [$course, $first, $second] = $this->makeCourseWithTwoLessons();
        $customer = User::factory()->role('customer')->create();
        Inscription::factory()->create(['user_id' => $customer->id, 'course_id' => $course->id]);

        $this->actingAs($customer)->post(route('customers.courses.realized'), ['lesson' => $first->id]);

        $this->actingAs($customer)
            ->get(route('customers.courses.player', $second->id))
            ->assertOk();
    }
}
