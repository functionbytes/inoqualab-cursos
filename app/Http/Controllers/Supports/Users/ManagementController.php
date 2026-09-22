<?php

namespace App\Http\Controllers\Supports\Users;

use App\Http\Controllers\Controller;
use App\Models\Course\CourseProgress;
use App\Models\Inscription;
use Illuminate\Http\Request;

class ManagementController extends Controller
{
    public function index(Request $request, $slack)
    {

        $inscription = Inscription::slack($slack);
        $course = $inscription->course;

        return view('supports.views.users.managements.index')->with([
            'inscription' => $inscription,
            'course' => $course,
        ]);
    }

    public function progressView($slack)
    {

        $inscription = Inscription::slack($slack);
        $progress = $inscription->progress;
        $user = $inscription->user;
        $course = $inscription->course;
        // with('chapter'): la vista agrupa por chapter_id y muestra
        // $chapter->title por lección -- sin esto es una query extra por
        // lección del curso (N+1).
        $class = $course->lessons()->with('chapter')->get();

        return view('supports.views.users.managements.progress')->with([
            'user' => $user,
            'course' => $course,
            'progress' => $progress,
            'class' => $class,
            'inscription' => $inscription,
        ]);

    }

    public function progressRestoreSingle($slack)
    {
        $inscription = null;
        $progress = CourseProgress::id($slack);
        $inscription = $progress->inscription;
        $progress->delete();

        return redirect()->route('support.enterprises.users.managements.progress.view', $inscription->slack);
    }

    public function progressRestore($slack)
    {
        $inscription = Inscription::slack($slack);
        $inscription->progress()->delete(); // Elimina todos los registros relacionados

        return redirect()->route('support.enterprises.users.managements.progress.view', $inscription->slack);
    }

    public function quizView($slack)
    {
        $inscription = Inscription::slack($slack);
        abort_unless($inscription instanceof Inscription, 404);

        $quizs = $inscription->quizs()->with('lesson')->get();

        return view('supports.views.users.managements.quizs')->with([
            'inscription' => $inscription,
            'course' => $inscription->course,
            'user' => $inscription->user,
            'quizs' => $quizs,
        ]);
    }

    public function quizRestore($slack)
    {
        $inscription = Inscription::slack($slack);
        abort_unless($inscription instanceof Inscription, 404);

        // Reinicia los intentos: el quiz se recrea cuando el estudiante vuelve a entrar.
        foreach ($inscription->quizs as $quiz) {
            $quiz->answers()->delete();
            $quiz->delete();
        }

        return redirect()->route('support.enterprises.users.managements.quiz.view', $inscription->slack);
    }

    public function examView($slack)
    {
        $inscription = Inscription::slack($slack);
        abort_unless($inscription instanceof Inscription, 404);

        return view('supports.views.users.managements.exam')->with([
            'inscription' => $inscription,
            'course' => $inscription->course,
            'user' => $inscription->user,
            'exam' => $inscription->exam,
        ]);
    }

    public function examRestore($slack)
    {
        $inscription = Inscription::slack($slack);
        abort_unless($inscription instanceof Inscription, 404);

        // Mismo reinicio que el "intentar de nuevo" del aula: puntaje en cero y respuestas fuera.
        $exam = $inscription->exam;

        if ($exam) {
            $exam->update(['correct' => 0, 'wrong' => 0, 'score' => 0]);
            $exam->answers()->delete();
        }

        return redirect()->route('support.enterprises.users.managements.exam.view', $inscription->slack);
    }
}
