<?php

namespace Tests\Feature\Customers;

use App\Models\Course\Course;
use App\Models\Exam\Exam;
use App\Models\Exam\ExamAnswer;
use App\Models\Exam\ExamQuestion;
use App\Models\Exam\ExamTopic;
use App\Models\Inscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Blinda la emisión de certificados: la nota del examen debe ser aciertos/total
 * (antes 100 - wrong/count daba 100 con 0 respuestas, permitiendo obtener el
 * certificado sin contestar visitando directamente el finish (GET)).
 */
class ExamSecurityTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{customer: User, exam: Exam, inscription: Inscription} */
    private function scenario(int $showAns = 10, int $perQMark = 7): array
    {
        $customer = User::factory()->role('customer')->create();
        $course = Course::factory()->create();
        $topic = ExamTopic::factory()->create([
            'course_id' => $course->id,
            'show_ans' => $showAns,
            'per_q_mark' => $perQMark,
        ]);
        $inscription = Inscription::factory()->create([
            'user_id' => $customer->id,
            'course_id' => $course->id,
        ]);
        $exam = Exam::factory()->create([
            'user_id' => $customer->id,
            'course_id' => $course->id,
            'topic_id' => $topic->id,
            'inscription_id' => $inscription->id,
            'correct' => 0,
            'wrong' => 0,
            'score' => 0,
        ]);

        return compact('customer', 'exam', 'inscription');
    }

    private function seedAnswers(Exam $exam, int $correct, int $total): void
    {
        $rows = [];
        for ($i = 0; $i < $total; $i++) {
            $question = ExamQuestion::create([
                'slack' => \Illuminate\Support\Str::random(10),
                'course_id' => $exam->course_id,
                'topic_id' => $exam->topic_id,
                'question' => 'Pregunta '.($i + 1),
                'a' => 'x', 'b' => 'y', 'c' => 'z', 'd' => 'w',
                'answer' => 'x',
                'available' => 1,
            ]);
            $rows[] = [
                'user_answer' => 'x',
                'question_id' => $question->id,
                'user_id' => $exam->user_id,
                'exam_id' => $exam->id,
                'course_id' => $exam->course_id,
                'topic_id' => $exam->topic_id,
                'answer' => 'x',
                'approved' => $i < $correct ? 1 : 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        ExamAnswer::insert($rows);
    }

    public function test_finishing_exam_without_answers_scores_zero_and_issues_no_certificate(): void
    {
        ['customer' => $customer, 'exam' => $exam, 'inscription' => $inscription] = $this->scenario();

        $this->actingAs($customer)->get(route('customers.exam.show', $exam->id));

        $this->assertEquals(0.0, (float) $exam->fresh()->score);
        $this->assertDatabaseMissing('certificates', ['inscription_id' => $inscription->id]);
    }

    public function test_score_is_correct_answers_over_total(): void
    {
        // passing 80: con 7/10 (=70) el alumno reprueba y no recibe certificado.
        ['customer' => $customer, 'exam' => $exam, 'inscription' => $inscription] = $this->scenario(10, 8);
        $this->seedAnswers($exam, correct: 7, total: 10);

        $this->actingAs($customer)->get(route('customers.exam.show', $exam->id));

        $this->assertEquals(70.0, (float) $exam->fresh()->score);
        $this->assertDatabaseMissing('certificates', ['inscription_id' => $inscription->id]);
    }
}
