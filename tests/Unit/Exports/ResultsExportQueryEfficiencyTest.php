<?php

namespace Tests\Unit\Exports;

use App\Exports\Distributors\ResultsExport as DistributorsResultsExport;
use App\Exports\Managers\ResultsExport as ManagersResultsExport;
use App\Exports\Supports\ResultsExport as SupportsResultsExport;
use App\Models\Course\Course;
use App\Models\Exam\Exam;
use App\Models\Exam\ExamAnswer;
use App\Models\Exam\ExamQuestion;
use App\Models\Exam\ExamTopic;
use App\Models\Inscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Regresión: Managers\ResultsExport, Supports\ResultsExport y
 * Distributors\ResultsExport resolvían el enunciado de cada pregunta con
 * ExamQuestion::id($row->question_id)->question DENTRO de map() -- una
 * consulta por cada respuesta del examen. Su hermana Enterprises\ResultsExport
 * ya precarga las preguntas en el constructor con un solo whereIn(). Un
 * examen de 50-100 preguntas disparaba 50-100 queries por descarga.
 */
class ResultsExportQueryEfficiencyTest extends TestCase
{
    use RefreshDatabase;

    private function makeExamWithAnswers(int $count): Exam
    {
        $course = Course::factory()->create();
        $user = User::factory()->create(['role' => 'customer']);
        $topic = ExamTopic::factory()->create(['course_id' => $course->id]);
        $inscription = Inscription::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);
        $exam = Exam::factory()->create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'topic_id' => $topic->id,
            'inscription_id' => $inscription->id,
        ]);

        for ($i = 1; $i <= $count; $i++) {
            $question = ExamQuestion::create([
                'slack' => Str::random(8),
                'course_id' => $course->id,
                'topic_id' => $topic->id,
                'question' => "Pregunta {$i}",
                'a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D',
                'answer' => 'a',
                'available' => 1,
            ]);

            ExamAnswer::create([
                'exam_id' => $exam->id,
                'course_id' => $course->id,
                'topic_id' => $topic->id,
                'user_id' => $user->id,
                'question_id' => $question->id,
                'user_answer' => 'a',
                'answer' => 'a',
                'type' => 'exam',
                'approved' => 1,
            ]);
        }

        return $exam;
    }

    private function assertMapDoesNotQueryPerRow(string $exportClass): void
    {
        $exam = $this->makeExamWithAnswers(3);
        $export = new $exportClass($exam);
        $rows = $exam->answers()->get();

        DB::flushQueryLog();
        DB::enableQueryLog();
        foreach ($rows as $row) {
            $export->map($row);
        }
        $queries = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertSame(0, $queries, "map() de {$exportClass} disparó {$queries} queries para 3 filas -- debería precargar las preguntas y no consultar nada aquí.");
    }

    public function test_managers_results_export_map_does_not_query_per_row(): void
    {
        $this->assertMapDoesNotQueryPerRow(ManagersResultsExport::class);
    }

    public function test_supports_results_export_map_does_not_query_per_row(): void
    {
        $this->assertMapDoesNotQueryPerRow(SupportsResultsExport::class);
    }

    public function test_distributors_results_export_map_does_not_query_per_row(): void
    {
        $this->assertMapDoesNotQueryPerRow(DistributorsResultsExport::class);
    }
}
