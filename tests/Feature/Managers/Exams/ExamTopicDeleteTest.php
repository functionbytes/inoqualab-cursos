<?php

namespace Tests\Feature\Managers\Exams;

use App\Models\Course\Course;
use App\Models\Exam\Exam;
use App\Models\Exam\ExamTopic;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Borrar un examen (ExamTopic, con soft delete) que ya rindieron alumnos dejaría
 * sus intentos históricos sin las preguntas asociadas. destroy() debe bloquearlo.
 */
class ExamTopicDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_cannot_delete_exam_with_student_attempts(): void
    {
        $course = Course::factory()->create();
        $topic = ExamTopic::factory()->create(['course_id' => $course->id]);
        $student = User::factory()->customer()->create();
        Exam::factory()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'topic_id' => $topic->id,
        ]);

        $manager = User::factory()->manager()->create();

        $this->actingAs($manager)
            ->delete(route('manager.courses.exam.destroy', $topic->slack))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('exam_topics', ['id' => $topic->id]);
    }

    public function test_can_delete_exam_without_attempts(): void
    {
        $course = Course::factory()->create();
        $topic = ExamTopic::factory()->create(['course_id' => $course->id]);
        $manager = User::factory()->manager()->create();

        $this->actingAs($manager)
            ->delete(route('manager.courses.exam.destroy', $topic->slack));

        $this->assertSoftDeleted('exam_topics', ['id' => $topic->id]);
    }

    public function test_delete_forbidden_without_permission(): void
    {
        $course = Course::factory()->create();
        $topic = ExamTopic::factory()->create(['course_id' => $course->id]);

        Role::findByName('manager')->revokePermissionTo('exams.delete');
        $manager = User::factory()->manager()->create();

        $this->actingAs($manager)
            ->delete(route('manager.courses.exam.destroy', $topic->slack))
            ->assertForbidden();

        $this->assertDatabaseHas('exam_topics', ['id' => $topic->id]);
    }
}
