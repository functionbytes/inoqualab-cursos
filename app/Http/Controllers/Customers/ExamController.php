<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Customers\Concerns\ResolvesInscription;
use App\Models\Course\Course;
use App\Models\Course\CourseLesson;
use App\Models\Course\CourseReview;
use App\Models\Exam\Exam;
use App\Models\Exam\ExamAnswer;
use App\Models\Exam\ExamQuestion;
use App\Models\Exam\ExamTopic;
use App\Models\Users\Certificate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamController extends Controller
{
    use ResolvesInscription;

    public function exam($slack)
    {
        $user = app('customer');
        $course = Course::slack($slack);
        abort_unless($course instanceof Course, 404);
        $inscription = $this->resolveInscription($user, $course->id);
        $this->assertInscriptionActive($inscription);
        // A3: el examen final solo se habilita con todas las lecciones culminadas
        $this->assertExamAccessible($course, $inscription);
        $topic = $course->examtopic;
        $class = $course->lessons;
        $count = $topic->show_ans;
        $progress = $inscription->progress;
        $questions = $topic->questions->shuffle()->take($count);

        // Datos del rail de capítulos/lecciones (mismo patrón que CoursesController::lesion)
        $chapters = $course->chapters()->with(['lessons' => function ($q) {
            $q->where('available', 1)->orderBy('position')->with('type');
        }])->get();

        $completedLessonIds = $inscription->progress()
            ->where('culminated', 1)
            ->pluck('lesson_id')
            ->all();

        $chapterProgress = $inscription->progress()
            ->selectRaw('chapter_id, count(*) as total')
            ->groupBy('chapter_id')
            ->pluck('total', 'chapter_id')
            ->all();

        $totalClass = $course->lessons()->count();
        $completedClass = count($completedLessonIds);
        $progressPercentage = $totalClass > 0 ? round($completedClass * 100 / $totalClass) : 0;
        $lastchapter = null;
        $lastlesson = null;
        $percent = $inscription->percent;
        $percents = $inscription->percent;

        $exam = $inscription->exam;
        $certificate = ($exam && $exam->score >= $this->passingScoreFor($exam)) ? $inscription->certificate : null;

        // Última lección del curso: destino del botón "Anterior" (no hay lección "siguiente" tras el examen)
        $lastCourseLesson = CourseLesson::where('course_id', $course->id)
            ->where('available', 1)
            ->orderByDesc('position')
            ->first();

        if ($exam === null) {
            $exam = Exam::create([
                'course_id' => $inscription->course_id,
                'inscription_id' => $inscription->id,
                'topic_id' => $topic->id,
                'user_id' => $user->id,
                'correct' => 0,
                'wrong' => 0,
                'score' => 0,
            ]);
        } else {
            // No rehacer un examen ya presentado que no admite reintentos: reabrir la URL
            // borraba el intento (y con ello el resultado). Se muestra el resultado previo.
            if (! $topic->quiz_again && $exam->answers()->exists()) {
                return redirect()->route('customers.exam.show', $exam->id);
            }
            $exam->update(['correct' => 0, 'wrong' => 0, 'score' => 0]);
            $exam->answers()->delete();
        }

        return view('customers.views.exams.exam', compact(
            'course', 'class', 'chapters', 'progress',
            'topic', 'questions', 'exam', 'user', 'count',
            'inscription', 'completedLessonIds', 'chapterProgress',
            'totalClass', 'completedClass', 'progressPercentage',
            'lastchapter', 'lastlesson', 'percent', 'percents', 'certificate',
            'lastCourseLesson'
        ));
    }

    public function store(Request $request, $id)
    {
        $user = app('customer');
        $topic = ExamTopic::id($id);
        abort_unless($topic instanceof ExamTopic, 404);
        $exam = Exam::where('id', $request->exam)->where('user_id', $user->id)->firstOrFail();

        $inscription = $this->resolveInscription($user, $exam->course_id);
        $this->assertInscriptionActive($inscription);

        $exam->answers()->delete();

        $unique_question = array_unique($request->question_id);
        $count = count($request->answer);
        $rows = [];
        $now = Carbon::now()->setTimezone('America/Bogota');

        for ($i = 1; $i <= $count; $i++) {
            $question = ExamQuestion::id($unique_question[$i]);
            // Los question_id llegan como inputs hidden manipulables: exigir que la
            // pregunta pertenezca AL TOPIC de este examen evita que un alumno
            // sustituya IDs por preguntas de otro topic/curso cuya respuesta conoce
            // y así apruebe de forma fraudulenta (mismo riesgo que el fix fb41023).
            abort_unless(
                $question instanceof ExamQuestion && (int) $question->topic_id === (int) $topic->id,
                404
            );
            $answerCustomer = (array) $request->answer[$i];
            $isMultiple = str_contains($question->answer, ',');

            if ($isMultiple) {
                $correct = explode(',', $question->answer);
                $approved = (count($answerCustomer) === count($correct))
                    && implode(',', $correct) === implode(',', $answerCustomer) ? 1 : 0;
            } else {
                $approved = ($question->answer === ($answerCustomer[0] ?? '')) ? 1 : 0;
            }

            $rows[] = [
                'user_answer' => implode(',', $answerCustomer),
                'question_id' => $unique_question[$i],
                'user_id' => $user->id,
                'exam_id' => $exam->id,
                'course_id' => $topic->course_id,
                'topic_id' => $topic->id,
                'answer' => $question->answer,
                'approved' => $approved,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        ExamAnswer::insert($rows);

        // D2: envío por AJAX -- el JS del examen reemplaza el contenido de
        // .quiz-wrap con el resultado sin recargar ni navegar (la URL se
        // queda en customers.exam.show, /exam/{id}; antes redirigía a
        // customers.exam.finish). Fallback al redirect normal si la petición
        // no es AJAX.
        if ($request->ajax() || $request->wantsJson()) {
            $data = $this->buildFinishData($exam->id);

            return response()->json([
                'success' => true,
                'html' => view('customers.partials.views.exams.exam-result', $data)->render(),
                'url' => route('customers.exam.show', $exam->id),
            ]);
        }

        return redirect()->route('customers.exam.show', $exam->id);
    }

    public function finish($id)
    {
        return view('customers.views.exams.finish', $this->buildFinishData($id));
    }

    /**
     * Datos del resultado del examen + el rail de capítulos/lecciones,
     * compartidos entre finish() (GET a la vista completa, p.ej. tras un
     * refresh) y store() (respuesta AJAX del envío, sin recargar la página).
     */
    private function buildFinishData($id): array
    {
        $user = app('customer');
        $exam = Exam::where('id', $id)->where('user_id', $user->id)->firstOrFail();

        $inscription = $this->resolveInscription($user, $exam->course_id);
        $this->assertInscriptionActive($inscription);
        $topic = $exam->topic;
        $course = $exam->course;
        $answers = $exam->answers;
        $count = $topic->show_ans;
        $passingScore = $count > 0 ? round(($topic->per_q_mark / $count) * 100, 2) : 100;
        $progress = $inscription->progress;

        // Una sola query para correct/wrong
        $counts = $exam->answers()->where('user_id', $user->id)
            ->selectRaw('approved, count(*) as total')
            ->groupBy('approved')
            ->pluck('total', 'approved');
        $wrong = $counts[0] ?? 0;
        $correct = $counts[1] ?? 0;
        // Nota = aciertos / total de preguntas del examen. Las no respondidas cuentan
        // como no-acierto: sin respuestas -> 0 (antes daba 100 y emitía certificado).
        $score = $count > 0 ? round(($correct / $count) * 100, 2) : 0;

        $certificate = DB::transaction(function () use ($exam, $inscription, $course, $user, $wrong, $correct, $score, $passingScore) {
            $exam->update(['wrong' => $wrong, 'correct' => $correct, 'score' => $score]);

            if ($score < $passingScore) {
                return null;
            }

            $certificate = $inscription->certificate;

            if (! $certificate) {
                $certificate = Certificate::create([
                    'slack' => $this->generate_slack('certificates'),
                    'certification_id' => $course->certification_id,
                    'certifier_id' => $course->certifier_id,
                    'course_id' => $inscription->course_id,
                    'user_id' => $user->id,
                    'exam_id' => $exam->id,
                    'inscription_id' => $inscription->id,
                    'start_at' => Carbon::now()->setTimezone('America/Bogota'),
                    'end_at' => Carbon::now()->setTimezone('America/Bogota')->addYear(),
                ]);
            }

            // Se cuentan lecciones DISTINTAS y se capa a 100, igual que en
            // CoursesController::createOrUpdateProgress. Sin esto, una inscripción
            // con progreso duplicado (course_progress no tiene índice único sobre
            // inscription_id + lesson_id) terminaba con porcentajes imposibles:
            // en la base hay una inscripción marcada al 531,25 %.
            $lessons = $course->lessons()->count();
            $completadas = $inscription->progress()->distinct()->count('lesson_id');

            $inscription->update([
                'enroll_culminated' => Carbon::now(),
                'culminated' => 1,
                'percent' => $lessons > 0
                    ? min(100, round(($completadas / $lessons) * 100, 2))
                    : 100,
            ]);

            return $certificate;
        });

        // Reseña previa del usuario para este curso (si ya calificó).
        $userReview = CourseReview::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        // Datos del rail de capítulos/lecciones (mismo patrón que CoursesController::lesion)
        $chapters = $course->chapters()->with(['lessons' => function ($q) {
            $q->where('available', 1)->orderBy('position')->with('type');
        }])->get();

        $completedLessonIds = $inscription->progress()
            ->where('culminated', 1)
            ->pluck('lesson_id')
            ->all();

        $chapterProgress = $inscription->progress()
            ->selectRaw('chapter_id, count(*) as total')
            ->groupBy('chapter_id')
            ->pluck('total', 'chapter_id')
            ->all();

        $totalClass = $course->lessons()->count();
        $completedClass = count($completedLessonIds);
        $progressPercentage = $totalClass > 0 ? round($completedClass * 100 / $totalClass) : 0;
        $lastchapter = null;
        $lastlesson = null;
        $percent = $inscription->percent;
        $percents = $inscription->percent;

        // Última lección del curso: destino del botón "Anterior" (no hay lección "siguiente" tras el examen)
        $lastCourseLesson = CourseLesson::where('course_id', $course->id)
            ->where('available', 1)
            ->orderByDesc('position')
            ->first();

        return compact(
            'user', 'course', 'topic', 'wrong', 'correct',
            'answers', 'score', 'count', 'certificate', 'exam', 'userReview', 'passingScore',
            'lastCourseLesson', 'inscription',
            'chapters', 'completedLessonIds', 'chapterProgress', 'totalClass', 'completedClass',
            'progressPercentage', 'lastchapter', 'lastlesson', 'percent', 'percents'
        );
    }

    public function tryagain($id)
    {
        $user = app('customer');
        $exam = Exam::where('id', $id)->where('user_id', $user->id)->firstOrFail();

        // Revalidar acceso activo (consistente con store/finish): sin esto se podía
        // resetear el examen aunque la inscripción hubiera expirado.
        $this->assertInscriptionActive($this->resolveInscription($user, $exam->course_id));

        // A5: respetar la configuración del topic — si no permite reintentos, bloquear
        if ($exam->topic && ! $exam->topic->quiz_again) {
            return redirect()->route('customers.exam.show', $exam->id)
                ->with('error', 'Este examen no permite reintentos.');
        }

        $exam->update(['wrong' => 0, 'correct' => 0, 'score' => 0]);
        $exam->answers()->delete();

        // El curso puede haberse borrado (soft delete) después de crear el examen.
        return $exam->course
            ? redirect()->route('customers.courses.exam', $exam->course->slack)
            : redirect()->route('customers.courses');
    }
}
