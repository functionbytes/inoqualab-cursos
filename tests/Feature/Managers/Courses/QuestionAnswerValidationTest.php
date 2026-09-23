<?php

namespace Tests\Feature\Managers\Courses;

use App\Models\Course\Course;
use App\Models\Course\CourseChapter;
use App\Models\Course\CourseLesson;
use App\Models\Exam\ExamQuestion;
use App\Models\Exam\ExamTopic;
use App\Models\Quiz\QuizQuestion;
use App\Models\Quiz\QuizTopic;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Regresión: Store/UpdateQuizQuestionRequest y Store/UpdateExamQuestionRequest
 * solo exigían `answer` como string no vacío, sin validar que coincidiera con
 * una opción real del tipo de pregunta ('a'..'d' para selección múltiple,
 * 'true'/'false' para Falso-Verdadero). Un `answer` fuera de esas claves
 * dejaba la pregunta sin ninguna respuesta correcta posible para el alumno,
 * sin ningún error visible al guardar.
 */
class QuestionAnswerValidationTest extends TestCase
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

    public function test_quiz_question_store_rejects_answer_outside_the_real_options(): void
    {
        $lesson = $this->makeLesson();
        $topic = QuizTopic::factory()->create(['course_id' => $this->course->id, 'lesson_id' => $lesson->id, 'type' => 1]);

        $this->actingAs($this->manager)
            ->postJson(route('manager.courses.quiz.questions.store'), [
                'topic' => $topic->slack,
                'question' => '¿Correcta?',
                'a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D',
                'answer' => 'z',
                'available' => 1,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('answer');
    }

    public function test_quiz_question_store_rejects_blank_options_on_multiple_choice(): void
    {
        $lesson = $this->makeLesson();
        $topic = QuizTopic::factory()->create(['course_id' => $this->course->id, 'lesson_id' => $lesson->id, 'type' => 1]);

        $this->actingAs($this->manager)
            ->postJson(route('manager.courses.quiz.questions.store'), [
                'topic' => $topic->slack,
                'question' => '¿Correcta?',
                'a' => 'A', 'b' => '', 'c' => 'C', 'd' => 'D',
                'answer' => 'a',
                'available' => 1,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('b');
    }

    public function test_quiz_question_store_accepts_true_false_answer_for_that_topic_type(): void
    {
        $lesson = $this->makeLesson();
        $topic = QuizTopic::factory()->create(['course_id' => $this->course->id, 'lesson_id' => $lesson->id, 'type' => 0]);

        $this->actingAs($this->manager)
            ->postJson(route('manager.courses.quiz.questions.store'), [
                'topic' => $topic->slack,
                'question' => '¿Verdadero o falso?',
                'answer' => 'true',
                'available' => 1,
            ])
            ->assertOk();

        $this->assertDatabaseHas('quiz_questions', [
            'topic_id' => $topic->id,
            'answer' => 'true',
        ]);
    }

    public function test_quiz_question_store_accepts_multiple_correct_options(): void
    {
        // Confirmado contra datos reales: más de la mitad de las preguntas de
        // selección múltiple reales tienen más de una opción correcta
        // ("a,d", "b,c", etc.) -- answer NO es una única letra.
        $lesson = $this->makeLesson();
        $topic = QuizTopic::factory()->create(['course_id' => $this->course->id, 'lesson_id' => $lesson->id, 'type' => 1]);

        $this->actingAs($this->manager)
            ->postJson(route('manager.courses.quiz.questions.store'), [
                'topic' => $topic->slack,
                'question' => '¿Cuáles son correctas?',
                'a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D',
                'answer' => 'a,d',
                'available' => 1,
            ])
            ->assertOk();

        $this->assertDatabaseHas('quiz_questions', [
            'topic_id' => $topic->id,
            'answer' => 'a,d',
        ]);
    }

    public function test_quiz_question_store_accepts_uppercase_true_false(): void
    {
        // Los datos reales usan 'TRUE'/'FALSE' en mayúsculas.
        $lesson = $this->makeLesson();
        $topic = QuizTopic::factory()->create(['course_id' => $this->course->id, 'lesson_id' => $lesson->id, 'type' => 0]);

        $this->actingAs($this->manager)
            ->postJson(route('manager.courses.quiz.questions.store'), [
                'topic' => $topic->slack,
                'question' => '¿Verdadero o falso?',
                'answer' => 'TRUE',
                'available' => 1,
            ])
            ->assertOk();
    }

    public function test_quiz_question_store_rejects_duplicate_letters_in_multiple_answer(): void
    {
        $lesson = $this->makeLesson();
        $topic = QuizTopic::factory()->create(['course_id' => $this->course->id, 'lesson_id' => $lesson->id, 'type' => 1]);

        $this->actingAs($this->manager)
            ->postJson(route('manager.courses.quiz.questions.store'), [
                'topic' => $topic->slack,
                'question' => '¿Correcta?',
                'a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D',
                'answer' => 'a,a',
                'available' => 1,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('answer');
    }

    public function test_quiz_question_update_rejects_answer_outside_the_real_options(): void
    {
        $lesson = $this->makeLesson();
        $topic = QuizTopic::factory()->create(['course_id' => $this->course->id, 'lesson_id' => $lesson->id, 'type' => 1]);
        $question = QuizQuestion::create([
            'slack' => (string) Str::uuid(),
            'topic_id' => $topic->id,
            'lesson_id' => $topic->lesson_id,
            'type' => 1,
            'question' => 'Original',
            'a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D',
            'answer' => 'a',
            'available' => 1,
        ]);

        $this->actingAs($this->manager)
            ->postJson(route('manager.courses.quiz.questions.update'), [
                'slack' => $question->slack,
                'question' => 'Actualizada',
                'a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D',
                'answer' => 'not-a-real-option',
                'available' => 1,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('answer');
    }

    public function test_exam_question_store_rejects_answer_outside_the_real_options(): void
    {
        $topic = ExamTopic::factory()->create(['course_id' => $this->course->id, 'type' => 1]);

        $this->actingAs($this->manager)
            ->postJson(route('manager.courses.exam.questions.store'), [
                'topic' => $topic->slack,
                'question' => '¿Cuál es correcta?',
                'a' => 'Uno', 'b' => 'Dos', 'c' => 'Tres', 'd' => 'Cuatro',
                'answer' => 'nope',
                'available' => 1,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('answer');
    }

    public function test_exam_question_update_rejects_blank_options_on_multiple_choice(): void
    {
        $topic = ExamTopic::factory()->create(['course_id' => $this->course->id, 'type' => 1]);
        $question = new ExamQuestion;
        $question->slack = (string) Str::uuid();
        $question->question = 'Original';
        $question->answer = 'a';
        $question->a = 'Uno';
        $question->b = 'Dos';
        $question->c = 'Tres';
        $question->d = 'Cuatro';
        $question->available = 1;
        $question->type = 1;
        $question->course_id = $this->course->id;
        $question->topic_id = $topic->id;
        $question->save();

        $this->actingAs($this->manager)
            ->postJson(route('manager.courses.exam.questions.update'), [
                'slack' => $question->slack,
                'question' => 'Actualizada',
                'a' => 'Uno', 'b' => '', 'c' => 'Tres', 'd' => 'Cuatro',
                'answer' => 'a',
                'available' => 1,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('b');
    }

    /**
     * Regresión: el <select> de "Respuesta correcta" del modal de edición
     * solo tiene opciones en minúsculas ('true'/'false'), pero los datos
     * reales de Falso/Verdadero se guardan en mayúsculas ('TRUE'/'FALSE') --
     * 154/154 preguntas reales de Quiz. Sin normalizar, edit() devolvía
     * 'FALSE' y el <select> no encontraba ninguna opción coincidente, dejando
     * la respuesta correcta invisible/sin precargar al editar.
     */
    public function test_quiz_question_edit_returns_answer_lowercased_for_the_select_options(): void
    {
        $lesson = $this->makeLesson();
        $topic = QuizTopic::factory()->create(['course_id' => $this->course->id, 'lesson_id' => $lesson->id, 'type' => 0]);
        $question = QuizQuestion::create([
            'slack' => (string) Str::uuid(),
            'topic_id' => $topic->id,
            'lesson_id' => $topic->lesson_id,
            'type' => 0,
            'question' => '¿Verdadero o falso?',
            'answer' => 'FALSE',
            'available' => 1,
        ]);

        $this->actingAs($this->manager)
            ->getJson(route('manager.courses.quiz.questions.edit', $question->slack))
            ->assertOk()
            ->assertJson(['answer' => 'false']);
    }

    public function test_exam_question_edit_returns_answer_lowercased_for_the_select_options(): void
    {
        $topic = ExamTopic::factory()->create(['course_id' => $this->course->id, 'type' => 0]);
        $question = new ExamQuestion;
        $question->slack = (string) Str::uuid();
        $question->question = '¿Verdadero o falso?';
        $question->answer = 'TRUE';
        $question->available = 1;
        $question->type = 0;
        $question->course_id = $this->course->id;
        $question->topic_id = $topic->id;
        $question->save();

        $this->actingAs($this->manager)
            ->getJson(route('manager.courses.exam.questions.edit', $question->slack))
            ->assertOk()
            ->assertJson(['answer' => 'true']);
    }
}
