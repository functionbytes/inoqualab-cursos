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
 * Verifica que la página de contenido del curso renderiza tras optimizar el N+1
 * (precómputo de completedLessonIds/chapterProgress + eager-load de lecciones).
 */
class CourseContentRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_page_renders_with_a_chapter_and_lesson(): void
    {
        $customer = User::factory()->role('customer')->create();
        $course = Course::factory()->create();

        $type = CourseType::create(['slack' => Str::random(10), 'title' => 'Clase', 'slug' => 'lesson']);
        $chapter = CourseChapter::factory()->create(['course_id' => $course->id]);
        CourseLesson::create([
            'slack' => Str::random(10),
            'course_id' => $course->id,
            'chapter_id' => $chapter->id,
            'type_id' => $type->id,
            'title' => 'Lección 1',
            'position' => 1,
            'available' => 1,
        ]);

        $inscription = Inscription::factory()->create([
            'user_id' => $customer->id,
            'course_id' => $course->id,
        ]);

        $this->actingAs($customer)
            ->get(route('customers.courses.content', $inscription->slack))
            ->assertOk();
    }
}
