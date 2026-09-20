<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Customers\Concerns\ResolvesInscription;
use App\Models\Course\CourseLesson;
use App\Models\Course\CourseProgress;
use App\Models\Quiz\Quiz;
use App\Models\Quiz\QuizAnswer;
use App\Models\Quiz\QuizQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    use ResolvesInscription;

    public function quiz($id)
    {

        $user = app('customer');
        $lesson = CourseLesson::findOrFail($id);
        $inscription = $this->resolveInscription($user, $lesson->course_id);
        $this->assertInscriptionActive($inscription);
        $this->assertLessonAccessible($lesson, $inscription, $user->id);
        $topic = $lesson->quiztopic;
        abort_unless($topic, 404, 'Cuestionario no configurado.');

        $count = $topic->show_ans;
        $course = $lesson->course;
        $questions = $topic->questions->shuffle()->take($count);

        $progress = $inscription->progress;

        // Datos del rail de capítulos/lecciones (mismo patrón que CoursesController::lesion)
        $chapters = $course->chapters()->with(['lessons' => function ($q) {
            $q->where('available', 1)->orderBy('position')->with('type');
        }])->get();

        $completedLessonIds = $inscription->progress()
            ->where('culminated', 1)
            ->whereNotNull('lesson_id')
            ->distinct()
            ->pluck('lesson_id')
            ->all();

        $chapterProgress = $inscription->progress()
            ->selectRaw('chapter_id, count(DISTINCT lesson_id) as total')
            ->groupBy('chapter_id')
            ->pluck('total', 'chapter_id')
            ->all();

        $totalClass = $course->lessons()->count();
        $completedClass = count($completedLessonIds);
        $progressPercentage = $totalClass > 0 ? min(100, round($completedClass * 100 / $totalClass)) : 0;
        $lastchapter = $lesson->chapter_id;
        $lastlesson = $lesson->id;
        $percent = $inscription->percent;
        $percents = $inscription->percent;
        $exam = $inscription->exam;
        $certificate = ($exam && $exam->score >= $this->passingScoreFor($exam)) ? $inscription->certificate : null;

        // Navegación libre entre lecciones del curso (independiente de responder el quiz)
        $prevLesson = CourseProgress::prevNext($lesson->id, 'prev');
        $nextLesson = CourseProgress::prevNext($lesson->id, 'next');

        $quiz = Quiz::where('lesson_id', $lesson->id)->where('user_id', $user->id)->first();

        if ($quiz === null) {
            $quiz = new Quiz;
            $quiz->correct = 0;
            $quiz->wrong = 0;
            $quiz->score = 0;
            $quiz->topic_id = $topic->id;
            $quiz->inscription_id = $inscription->id;
            $quiz->lesson_id = $lesson->id;
            $quiz->course_id = $course->id;
            $quiz->user_id = $user->id;
            $quiz->save();
        } else {
            // No rehacer un quiz ya presentado que no admite reintentos: reabrir la URL
            // borraba el intento y esquivaba el límite quiz_again. Se muestra el resultado.
            if (! $topic->quiz_again && $quiz->answers()->exists()) {
                return redirect()->route('customers.quiz.show', $quiz->id);
            }
            $quiz->update(['correct' => 0, 'wrong' => 0, 'score' => 0]);
            $quiz->answers()->delete();
        }

        $answers = [];

        return view('customers.views.quizs.quiz', [
            'course' => $course,
            'lesson' => $lesson,
            'chapters' => $chapters,
            'progress' => $progress,
            'topic' => $topic,
            'answers' => $answers,
            'questions' => $questions,
            'user' => $user,
            'quiz' => $quiz,
            'count' => $count,
            'inscription' => $inscription,
            'completedLessonIds' => $completedLessonIds,
            'chapterProgress' => $chapterProgress,
            'totalClass' => $totalClass,
            'completedClass' => $completedClass,
            'progressPercentage' => $progressPercentage,
            'lastchapter' => $lastchapter,
            'lastlesson' => $lastlesson,
            'percent' => $percent,
            'percents' => $percents,
            'exam' => $exam,
            'certificate' => $certificate,
            'prevLesson' => $prevLesson,
            'nextLesson' => $nextLesson,
        ]);

    }

    public function store(Request $request, $id)
    {

        $user = app('customer');

        // A2: el quiz debe pertenecer al usuario autenticado (no se confía en el request)
        $quiz = Quiz::where('id', $request->quiz)->where('user_id', $user->id)->firstOrFail();
        $topic = $quiz->topic;

        $questionIds = $request->question_id ?? [];
        $userAnswers = $request->answer ?? [];

        // A1: la calificación se hace contra la respuesta almacenada en BD, nunca contra el cliente
        // A3: los question_id son inputs hidden manipulables; se restringen AL TOPIC de este quiz
        // para que no se puedan calificar preguntas de otro topic (las ajenas se descartan en
        // processQuizAnswers vía el continue cuando $questions->get() devuelve null).
        $questions = QuizQuestion::whereIn('id', $questionIds)
            ->where('topic_id', $topic->id)
            ->get()
            ->keyBy('id');
        $answers = $this->processQuizAnswers($topic, $quiz, $questionIds, $userAnswers, $questions);

        QuizAnswer::insert($answers);

        // D2: envío por AJAX -- el JS del quiz reemplaza el contenido de
        // .quiz-wrap con el resultado sin recargar ni navegar (la URL se
        // queda en customers.quiz.show, /quiz/{id}; antes redirigía a
        // customers.quiz.finish). Devuelve el mismo HTML que pintaría la
        // vista de resultado, ya renderizado. Si la petición no viene por
        // AJAX (JS deshabilitado, cliente viejo) se conserva el redirect de
        // siempre como fallback funcional.
        if ($request->ajax() || $request->wantsJson()) {
            $data = $this->buildFinishData($quiz->id);

            return response()->json([
                'success' => true,
                'html' => view('customers.partials.views.quizs.quiz-result', $data)->render(),
                'url' => route('customers.quiz.show', $quiz->id),
            ]);
        }

        return redirect()->route('customers.quiz.show', $quiz->id);

    }

    private function processQuizAnswers($topic, $quiz, $questionIds, $userAnswers, $questions)
    {

        $answers = [];

        foreach ($questionIds as $index => $questionId) {

            $question = $questions->get($questionId);

            if (! $question) {
                continue;
            }

            $userAnswer = $userAnswers[$index] ?? null;
            $correctAnswer = $question->answer;   // ← fuente de verdad: la BD

            $isCorrect = $this->checkAnswer($question->type, $userAnswer, $correctAnswer);

            $answers[] = [
                'user_answer' => $question->type ? implode(',', (array) $userAnswer) : $userAnswer,
                'question_id' => $question->id,
                'user_id' => $quiz->user_id,
                'quiz_id' => $quiz->id,
                'lesson_id' => $topic->lesson_id,
                'topic_id' => $topic->id,
                'answer' => $correctAnswer,
                'approved' => $isCorrect ? 1 : 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        return $answers;

    }

    private function checkAnswer($isMultipleChoice, $userAnswer, $correctAnswer)
    {

        if ($isMultipleChoice == 1) {
            $userAnswers = implode(',', $userAnswer);
            $correctAnswers = $correctAnswer;

            return $correctAnswers == $userAnswers;
        } else {
            return strtolower($userAnswer) == strtolower($correctAnswer);
        }
    }

    public function finish($id)
    {
        return view('customers.views.quizs.finish', $this->buildFinishData($id));
    }

    /**
     * Datos del resultado del quiz + el rail de capítulos/lecciones, compartidos
     * entre finish() (GET a la vista completa, p.ej. tras un refresh) y
     * store() (respuesta AJAX del envío, sin recargar la página).
     */
    private function buildFinishData($id): array
    {

        $user = app('customer');
        // A2: solo el dueño del quiz puede ver/recalcular su resultado
        $quiz = Quiz::where('id', $id)->where('user_id', $user->id)->firstOrFail();

        $lesson = $quiz->lesson;
        $course = $lesson->course;
        $topic = $quiz->topic;
        $count = $topic->show_ans;
        $passingScore = $count > 0 ? round(($topic->per_q_mark / $count) * 100, 2) : 100;
        $inscription = $quiz->inscription;

        $wrong = $quiz->answers()->where('approved', 0)->count();
        $correct = $quiz->answers()->where('approved', 1)->count();

        // Nota = aciertos / total de preguntas. Las no respondidas cuentan como no-acierto
        // (antes 100 - wrong/count daba 100 con 0 respuestas).
        $score = $count > 0 ? round(($correct / $count) * 100, 2) : 0;

        $quiz->update([
            'wrong' => $wrong,
            'correct' => $correct,
            'score' => $score,
        ]);

        $nextLesson = CourseProgress::prevNext($lesson->id, 'next');
        $prevLesson = CourseProgress::prevNext($lesson->id, 'prev');

        // Datos del rail de capítulos/lecciones (mismo patrón que CoursesController::lesion)
        $chapters = $course->chapters()->with(['lessons' => function ($q) {
            $q->where('available', 1)->orderBy('position')->with('type');
        }])->get();

        $completedLessonIds = $inscription->progress()
            ->where('culminated', 1)
            ->whereNotNull('lesson_id')
            ->distinct()
            ->pluck('lesson_id')
            ->all();

        $chapterProgress = $inscription->progress()
            ->selectRaw('chapter_id, count(DISTINCT lesson_id) as total')
            ->groupBy('chapter_id')
            ->pluck('total', 'chapter_id')
            ->all();

        $totalClass = $course->lessons()->count();
        $completedClass = count($completedLessonIds);
        $progressPercentage = $totalClass > 0 ? min(100, round($completedClass * 100 / $totalClass)) : 0;
        $lastchapter = $lesson->chapter_id;
        $lastlesson = $lesson->id;
        $percent = $inscription->percent;
        $percents = $inscription->percent;
        $examModel = $inscription->exam;
        $certificate = ($examModel && $examModel->score >= $this->passingScoreFor($examModel)) ? $inscription->certificate : null;

        return [
            'user' => $user,
            'course' => $course,
            'lesson' => $lesson,
            'inscription' => $inscription,
            'topic' => $topic,
            'wrong' => $wrong,
            'correct' => $correct,
            'score' => $score,
            'count' => $count,
            'chapters' => $chapters,
            'completedLessonIds' => $completedLessonIds,
            'chapterProgress' => $chapterProgress,
            'totalClass' => $totalClass,
            'completedClass' => $completedClass,
            'progressPercentage' => $progressPercentage,
            'lastchapter' => $lastchapter,
            'lastlesson' => $lastlesson,
            'percent' => $percent,
            'percents' => $percents,
            'exam' => $examModel,
            'certificate' => $certificate,
            'passingScore' => $passingScore,
            'nextLesson' => $nextLesson,
            'prevLesson' => $prevLesson,
            'quiz' => $quiz,
        ];

    }

    public function tryagain($id)
    {

        $user = app('customer');
        // A2: solo el dueño puede reintentar su quiz
        $quiz = Quiz::where('id', $id)->where('user_id', $user->id)->firstOrFail();

        // Revalidar acceso activo (consistente con store/finish): sin esto se podía
        // resetear el quiz aunque la inscripción hubiera expirado.
        $this->assertInscriptionActive($this->resolveInscription($user, $quiz->course_id));

        // A5: respetar la configuración del topic — si no permite reintentos, bloquear
        if ($quiz->topic && ! $quiz->topic->quiz_again) {
            return redirect()->route('customers.quiz.show', $quiz->id)
                ->with('error', 'Este cuestionario no permite reintentos.');
        }

        $quiz->update(['wrong' => 0, 'correct' => 0, 'score' => 0]);
        $quiz->answers()->delete();

        return redirect()->route('customers.courses.quiz', $quiz->lesson_id);

    }

    public function realized(Request $request)
    {

        $user = app('customer');
        $lesson = CourseLesson::findOrFail($request->lesson);
        $inscription = $this->resolveInscription($user, $lesson->course_id);
        $this->assertInscriptionActive($inscription);

        // Impide marcar lecciones fuera de orden por POST (desbloquearía el examen).
        $this->assertLessonAccessible($lesson, $inscription, $user->id);

        // H3: si la lección es un cuestionario (type 6), exigir haberlo APROBADO antes de
        // culminarla; si no, un POST directo a realized avanzaba aun con el quiz reprobado.
        if ($lesson->type_id == 6 && ($quizTopic = $lesson->quiztopic)) {
            $quiz = Quiz::where('lesson_id', $lesson->id)->where('user_id', $user->id)->first();
            $passing = $quizTopic->show_ans > 0 ? round(($quizTopic->per_q_mark / $quizTopic->show_ans) * 100, 2) : 100;
            abort_unless($quiz && $quiz->score >= $passing, 403, 'Debes aprobar el cuestionario para continuar.');
        }

        $course = $inscription->course;
        $lessons = $course->lessons;
        $chapter = $lesson->chapter;
        $exam = $inscription->exam;

        // createOrFirst (no firstOrCreate) para que sea seguro ante concurrencia
        // real: si dos requests llegan a la vez para la misma (inscription_id,
        // lesson_id), la perdedora del INSERT recibe la violación del índice
        // único y createOrFirst la resuelve releyendo la fila ganadora, en vez
        // de reventar con un 500 (firstOrCreate no captura esa excepción).
        DB::transaction(function () use ($inscription, $lesson, $user, $course, $chapter, $lessons) {
            $progress = CourseProgress::query()->createOrFirst(
                [
                    'inscription_id' => $inscription->id,
                    'lesson_id' => $lesson->id,
                ],
                [
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                    'chapter_id' => $chapter->id,
                    'culminated' => 1,
                ]
            );

            if (! $progress->wasRecentlyCreated) {
                return;
            }

            $lessonsCount = count($lessons);
            $inscription->update([
                'percent' => $lessonsCount > 0
                    ? round((count($inscription->progress) * (100 / $lessonsCount)), 2)
                    : 0,
            ]);
        });

        $nextLesson = CourseProgress::prevNext($lesson->id, 'next');

        if ($nextLesson !== 'true') {
            return redirect()->route($nextLesson->type_id == 6 ? 'customers.courses.quiz' : 'customers.courses.lesion', $nextLesson->id);
        } elseif (count($inscription->progress) == count($lessons)) {
            // Curso completado con un quiz como última lección: crear el examen
            // igual que lo hace el flujo de lecciones normales (dead-end si no).
            $exam = $exam ?: $this->ensureExamCreated($inscription, $course);

            if ($exam && $exam->score < $this->passingScoreFor($exam)) {
                return redirect()->route('customers.courses.exam', $course->slack);
            }

            return redirect()->route('customers.courses.content', $inscription->slack);
        } else {
            return redirect()->route('customers.courses.content', $inscription->slack);
        }

    }
}
