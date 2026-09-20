<?php

namespace Tests\Feature\Supports\Users;

use App\Models\Course\Course;
use App\Models\Course\CourseChapter;
use App\Models\Course\CourseLesson;
use App\Models\Course\CourseProgress;
use App\Models\Course\CourseType;
use App\Models\Exam\Exam;
use App\Models\Exam\ExamTopic;
use App\Models\Inscription;
use App\Models\Quiz\Quiz;
use App\Models\Quiz\QuizTopic;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Regresion: progressRestore/progressRestoreSingle/quizRestore/examRestore
 * son operaciones destructivas (borran progreso/quiz/examen real) pero
 * estaban mapeadas como Route::get(). El JS de .confirm-delete
 * (public/supports/js/includes/scripts.js) ya envia POST+_method=DELETE con
 * CSRF -- con la ruta como GET, el click normal del boton daba SIEMPRE 405
 * Method Not Allowed (confirmado en navegador antes del fix), y ademas la
 * ruta GET seguia siendo alcanzable directo (vulnerable a CSRF por
 * <img>/prefetch, sin token). Ahora son Route::delete().
 */
class ManagementControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $support;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->support = User::factory()->support()->create();
    }

    private function makeLesson(Course $course): CourseLesson
    {
        $type = CourseType::firstOrCreate(['slug' => 'lesson'], [
            'slack' => Str::random(10),
            'title' => 'Clase',
            'slug' => 'lesson',
        ]);
        $chapter = CourseChapter::factory()->create(['course_id' => $course->id]);

        return CourseLesson::create([
            'slack' => Str::random(10),
            'course_id' => $course->id,
            'chapter_id' => $chapter->id,
            'type_id' => $type->id,
            'title' => 'Leccion de prueba',
            'position' => 1,
            'available' => 1,
        ]);
    }

    public function test_progress_restore_accepts_delete_and_removes_progress(): void
    {
        $inscription = Inscription::factory()->create();
        CourseProgress::create([
            'user_id' => $inscription->user_id,
            'course_id' => $inscription->course_id,
            'inscription_id' => $inscription->id,
        ]);

        $this->actingAs($this->support)
            ->delete(route('support.enterprises.users.managements.progress.restore', $inscription->slack))
            ->assertRedirect(route('support.enterprises.users.managements.progress.view', $inscription->slack));

        $this->assertSame(0, $inscription->progress()->count());
    }

    public function test_progress_restore_no_longer_accepts_get(): void
    {
        $inscription = Inscription::factory()->create();

        $this->actingAs($this->support)
            ->get(route('support.enterprises.users.managements.progress.restore', $inscription->slack))
            ->assertMethodNotAllowed();
    }

    public function test_progress_restore_single_accepts_delete(): void
    {
        $inscription = Inscription::factory()->create();
        $progress = CourseProgress::create([
            'user_id' => $inscription->user_id,
            'course_id' => $inscription->course_id,
            'inscription_id' => $inscription->id,
        ]);

        $this->actingAs($this->support)
            ->delete(route('support.enterprises.users.managements.progress.restore.single', $progress->id))
            ->assertRedirect(route('support.enterprises.users.managements.progress.view', $inscription->slack));

        $this->assertNull(CourseProgress::find($progress->id));
    }

    public function test_quiz_restore_accepts_delete_and_removes_quizzes(): void
    {
        $inscription = Inscription::factory()->create();
        $lesson = $this->makeLesson($inscription->course);
        // QuizTopicFactory no rellena lesson_id/course_id (NOT NULL): creado a mano.
        $topic = QuizTopic::create([
            'slack' => Str::random(10),
            'title' => 'Topic de prueba',
            'per_q_mark' => 1,
            'available' => 1,
            'show_ans' => 1,
            'quiz_again' => 0,
            'lesson_id' => $lesson->id,
            'course_id' => $inscription->course_id,
        ]);

        Quiz::create([
            'user_id' => $inscription->user_id,
            'course_id' => $inscription->course_id,
            'lesson_id' => $lesson->id,
            'topic_id' => $topic->id,
            'inscription_id' => $inscription->id,
            'correct' => 1,
            'wrong' => 1,
            'score' => 50,
        ]);

        $this->actingAs($this->support)
            ->delete(route('support.enterprises.users.managements.quiz.restore', $inscription->slack))
            ->assertRedirect(route('support.enterprises.users.managements.quiz.view', $inscription->slack));

        $this->assertSame(0, $inscription->fresh()->quizs()->count());
    }

    public function test_quiz_restore_no_longer_accepts_get(): void
    {
        $inscription = Inscription::factory()->create();

        $this->actingAs($this->support)
            ->get(route('support.enterprises.users.managements.quiz.restore', $inscription->slack))
            ->assertMethodNotAllowed();
    }

    public function test_exam_restore_accepts_delete_and_resets_score(): void
    {
        $inscription = Inscription::factory()->create();
        // ExamTopicFactory no rellena course_id (NOT NULL): creado a mano.
        $topic = ExamTopic::create([
            'slack' => Str::random(10),
            'title' => 'Topic de prueba',
            'per_q_mark' => 1,
            'show_ans' => 1,
            'quiz_again' => 0,
            'available' => 1,
            'course_id' => $inscription->course_id,
        ]);

        $exam = Exam::create([
            'user_id' => $inscription->user_id,
            'course_id' => $inscription->course_id,
            'topic_id' => $topic->id,
            'inscription_id' => $inscription->id,
            'correct' => 7,
            'wrong' => 3,
            'score' => 70,
        ]);

        $this->actingAs($this->support)
            ->delete(route('support.enterprises.users.managements.exam.restore', $inscription->slack))
            ->assertRedirect(route('support.enterprises.users.managements.exam.view', $inscription->slack));

        $this->assertEquals(0, $exam->fresh()->score);
    }

    public function test_exam_restore_no_longer_accepts_get(): void
    {
        $inscription = Inscription::factory()->create();

        $this->actingAs($this->support)
            ->get(route('support.enterprises.users.managements.exam.restore', $inscription->slack))
            ->assertMethodNotAllowed();
    }
}
