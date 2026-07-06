<?php

namespace Tests\Feature\Managers\Courses;

use App\Models\Course\Course;
use App\Models\Course\CourseChapter;
use App\Models\Course\CourseLesson;
use App\Models\Exam\ExamTopic;
use App\Models\Quiz\QuizTopic;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Duplicado de curso. Bloquea la regresión del bug crítico: los `use` de
 * ExamQuestion/QuizQuestion tenían el namespace equivocado y `action()` daba un
 * "Class not found" (500) en cuanto el curso tenía examen o quiz.
 */
class CourseDuplicateTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicating_a_course_with_exam_and_quiz_copies_the_structure(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $manager = User::factory()->manager()->create();

        $course = Course::factory()->create(['title' => 'CURSO ORIGINAL']);
        $typeId = DB::table('course_types')->insertGetId(['title' => 'Video', 'slug' => 'video-'.Str::random(4)]);

        // Examen del curso (ejercita el path ExamQuestion::where que daba 500).
        ExamTopic::factory()->create(['course_id' => $course->id]);

        $chapter = CourseChapter::factory()->create(['course_id' => $course->id]);
        $lesson = new CourseLesson;
        $lesson->slack = Str::random(10);
        $lesson->title = 'Clase 1';
        $lesson->available = 1;
        $lesson->position = 1;
        $lesson->type_id = $typeId;
        $lesson->course_id = $course->id;
        $lesson->chapter_id = $chapter->id;
        $lesson->save();

        // Quiz de la lección (ejercita el path QuizQuestion::where que daba 500).
        QuizTopic::factory()->create(['course_id' => $course->id, 'lesson_id' => $lesson->id]);

        $response = $this->actingAs($manager)->post(route('manager.courses.action'), [
            'course' => $course->slack,
            'duplicate' => 'CURSO COPIA',
        ]);

        $response->assertRedirect(route('manager.courses'));

        $copy = Course::where('title', 'CURSO COPIA')->first();
        $this->assertNotNull($copy, 'El curso duplicado no se creó (posible 500 en action()).');
        $this->assertNotSame($course->id, $copy->id);

        // La estructura se copió al curso nuevo.
        $this->assertSame(1, CourseChapter::where('course_id', $copy->id)->count());
        $this->assertSame(1, CourseLesson::where('course_id', $copy->id)->count());
        $this->assertSame(1, ExamTopic::where('course_id', $copy->id)->count());
        $this->assertSame(1, QuizTopic::where('course_id', $copy->id)->count());
    }

    public function test_duplicate_requires_create_permission(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        // Usuario manager (columna) pero sin permisos Spatie.
        $user = User::factory()->manager()->create();
        $user->syncRoles([]);
        $user->syncPermissions([]);
        $course = Course::factory()->create();

        $this->actingAs($user)
            ->post(route('manager.courses.action'), [
                'course' => $course->slack,
                'duplicate' => 'X',
            ])
            ->assertForbidden();
    }
}
