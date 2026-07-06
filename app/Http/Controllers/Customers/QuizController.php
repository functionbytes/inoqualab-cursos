<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Customers\Concerns\ResolvesInscription;
use App\Models\Course\CourseLesson;
use App\Models\Course\CourseProgress;
use App\Models\Quiz\Quiz;
use App\Models\Quiz\QuizAnswer;
use App\Models\Quiz\QuizQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
        $chapters = $course->chapter;
        $questions = $topic->questions->shuffle()->take($count);

        $progress = $inscription->progress;

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
        ]);

    }

    public function store(Request $request, $id): RedirectResponse
    {

        $user = app('customer');

        // A2: el quiz debe pertenecer al usuario autenticado (no se confía en el request)
        $quiz = Quiz::where('id', $request->quiz)->where('user_id', $user->id)->firstOrFail();
        $topic = $quiz->topic;

        $questionIds = $request->question_id ?? [];
        $userAnswers = $request->answer ?? [];

        // A1: la calificación se hace contra la respuesta almacenada en BD, nunca contra el cliente
        $questions = QuizQuestion::findMany($questionIds)->keyBy('id');
        $answers = $this->processQuizAnswers($topic, $quiz, $questionIds, $userAnswers, $questions);

        QuizAnswer::insert($answers);

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

        $score = $count > 0 ? ($count == $correct ? 100 : round(100 - ($wrong / $count * 100), 2)) : 0;

        $quiz->update([
            'wrong' => $wrong,
            'correct' => $correct,
            'score' => $score,
        ]);

        $nextLesson = CourseProgress::prevNext($lesson->id, 'next');

        return view('customers.views.quizs.finish', [
            'user' => $user,
            'course' => $course,
            'lesson' => $lesson,
            'inscription' => $inscription,
            'topic' => $topic,
            'wrong' => $wrong,
            'correct' => $correct,
            'score' => $score,
            'count' => $count,
            'passingScore' => $passingScore,
            'nextLesson' => $nextLesson,
            'quiz' => $quiz,
        ]);

    }

    public function tryagain($id)
    {

        $user = app('customer');
        // A2: solo el dueño puede reintentar su quiz
        $quiz = Quiz::where('id', $id)->where('user_id', $user->id)->firstOrFail();

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

        $course = $inscription->course;
        $lessons = $course->lessons;
        $chapter = $lesson->chapter;
        $exam = $inscription->exam;

        $progress = $inscription->progress()
            ->where('lesson_id', $lesson->id)
            ->first();

        if (! $progress) {

            $progress = $inscription->progress()->create([
                'lesson_id' => $lesson->id,
                'user_id' => $user->id,
                'course_id' => $course->id,
                'chapter_id' => $chapter->id,
                'inscription_id' => $inscription->id,
                'culminated' => 1,
            ]);

            $lessonsCount = count($lessons);
            $inscription->update([
                'percent' => $lessonsCount > 0
                    ? round((count($inscription->progress) * (100 / $lessonsCount)), 2)
                    : 0,
            ]);

        }

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
