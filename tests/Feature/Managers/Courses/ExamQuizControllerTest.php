<?php

namespace Tests\Feature\Managers\Courses;

use App\Models\Course\Course;
use App\Models\Course\CourseChapter;
use App\Models\Course\CourseLesson;
use App\Models\Exam\ExamQuestion;
use App\Models\Exam\ExamTopic;
use App\Models\Quiz\QuizTopic;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Caracterización del comportamiento actual de Exam/Quiz (topics y preguntas):
 * fija las diferencias reales entre ambos (examen uppercasea y usa course_id;
 * quiz usa lesson_id) antes de cualquier refactor de unificación.
 */
class ExamQuizControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected Course $course;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->manager = User::factory()->manager()->create();
        $this->course = Course::factory()->create();
    }

    private function makeLesson(): CourseLesson
    {
        $chapter = CourseChapter::factory()->create(['course_id' => $this->course->id]);
        $typeId = DB::table('course_types')->insertGetId(['title' => 'Video', 'slug' => 'video-'.Str::random(4)]);
        $lesson = new CourseLesson;
        $lesson->slack = Str::random(10);
        $lesson->title = 'Clase';
        $lesson->available = 1;
        $lesson->position = 1;
        $lesson->type_id = $typeId;
        $lesson->course_id = $this->course->id;
        $lesson->chapter_id = $chapter->id;
        $lesson->save();

        return $lesson;
    }

    // ── Exam topic ────────────────────────────────────────────────────────────

    public function test_exam_topic_store_uppercases_title_and_sets_course(): void
    {
        $this->actingAs($this->manager)
            ->postJson(route('manager.courses.exam.store'), [
                'course' => $this->course->slack,
                'title' => 'examen final',
                'mark' => 2,
                'question' => 10,
                'duration' => 1,
                'day' => 5,
                'available' => 1,
                'type' => 1,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('exam_topics', [
            'course_id' => $this->course->id,
            'title' => 'EXAMEN FINAL',
            'per_q_mark' => 2,
            'show_ans' => 10,
            'quiz_again' => 1,
        ]);
    }

    public function test_exam_topic_destroy_removes_its_questions(): void
    {
        $topic = ExamTopic::factory()->create(['course_id' => $this->course->id, 'type' => 1]);
        $question = new ExamQuestion;
        $question->slack = Str::random(10);
        $question->question = '¿?';
        $question->answer = 'a';
        $question->available = 1;
        $question->type = 1;
        $question->course_id = $this->course->id;
        $question->topic_id = $topic->id;
        $question->save();

        $this->actingAs($this->manager)
            ->delete(route('manager.courses.exam.destroy', $topic->slack))
            ->assertRedirect();

        $this->assertSoftDeleted('exam_topics', ['id' => $topic->id]);
        $this->assertSoftDeleted('exam_questions', ['id' => $question->id]);
    }

    public function test_exam_question_store_sets_options_and_course(): void
    {
        $topic = ExamTopic::factory()->create(['course_id' => $this->course->id, 'type' => 1]);

        $this->actingAs($this->manager)
            ->postJson(route('manager.courses.exam.questions.store'), [
                'topic' => $topic->slack,
                'question' => '¿Cuál es correcta?',
                'a' => 'Uno', 'b' => 'Dos', 'c' => 'Tres', 'd' => 'Cuatro',
                'answer' => 'a',
                'available' => 1,
            ])
            ->assertOk();

        $this->assertDatabaseHas('exam_questions', [
            'topic_id' => $topic->id,
            'course_id' => $this->course->id,
            'a' => 'Uno', 'd' => 'Cuatro',
            'answer' => 'a',
            'type' => 1,
        ]);
    }

    // ── Quiz topic ────────────────────────────────────────────────────────────

    public function test_quiz_topic_store_sets_lesson_and_keeps_title_case(): void
    {
        $lesson = $this->makeLesson();

        $this->actingAs($this->manager)
            ->postJson(route('manager.courses.quiz.store'), [
                'course' => $this->course->slack,
                'lesson' => $lesson->id,
                'title' => 'quiz modulo 1',
                'mark' => 1,
                'question' => 8,
                'duration' => 1,
                'day' => 3,
                'available' => 1,
                'type' => 1,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        // Quiz NO uppercasea (a diferencia del examen) y guarda lesson_id.
        $this->assertDatabaseHas('quiz_topics', [
            'course_id' => $this->course->id,
            'lesson_id' => $lesson->id,
            'title' => 'quiz modulo 1',
        ]);
    }

    public function test_quiz_question_store_sets_lesson_not_course(): void
    {
        $lesson = $this->makeLesson();
        $topic = QuizTopic::factory()->create([
            'course_id' => $this->course->id,
            'lesson_id' => $lesson->id,
            'type' => 1,
        ]);

        $this->actingAs($this->manager)
            ->postJson(route('manager.courses.quiz.questions.store'), [
                'topic' => $topic->slack,
                'question' => '¿Correcta?',
                'a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D',
                'answer' => 'b',
                'available' => 1,
            ])
            ->assertOk();

        $this->assertDatabaseHas('quiz_questions', [
            'topic_id' => $topic->id,
            'lesson_id' => $lesson->id,
            'answer' => 'b',
            'type' => 1,
        ]);
    }

    // ── Autorización ──────────────────────────────────────────────────────────

    public function test_exam_store_requires_permission(): void
    {
        $user = User::factory()->manager()->create();
        $user->syncRoles([]);
        $user->syncPermissions([]);

        $this->actingAs($user)
            ->postJson(route('manager.courses.exam.store'), [
                'course' => $this->course->slack,
                'title' => 'x',
                'available' => 1,
                'type' => 1,
            ])
            ->assertForbidden();
    }
}
