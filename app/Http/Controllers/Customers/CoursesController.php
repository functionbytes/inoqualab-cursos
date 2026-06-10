<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Customers\Concerns\ResolvesInscription;
use App\Models\Course\Course;
use App\Models\Course\CourseLesson;
use App\Models\Course\CourseProgress;
use App\Models\Course\CourseReview;
use App\Models\Exam\Exam;
use App\Models\Inscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CoursesController extends Controller
{
    use ResolvesInscription;

    public function index()
    {

        $user = app('customer');
        $courses = $user->inscriptions()->with(['course.media', 'certificate'])->get();

        return view('customers.views.courses.index')->with([
            'user' => $user,
            'courses' => $courses,
        ]);
    }

    public function content($slack)
    {

        $user = app('customer');
        $inscription = Inscription::where('slack', $slack)->where('user_id', $user->id)->firstOrFail();
        $course = $inscription->course;
        $exam = $inscription->exam;
        $progress = $inscription->progress;

        $certificate = null;

        if ($exam && $exam->score >= $this->passingScoreFor($exam)) {
            $certificate = $inscription->certificate;
        } elseif ($progress->count() >= 100) {
            $exam = $this->createExamIfNeeded($inscription, $course);
        }

        rescue(fn () => Cache::put('inscription'.$user->slack, $inscription->slack, 6000), null, false);

        return view('customers.views.courses.content', [
            'course' => $course,
            'inscription' => $inscription,
            'exam' => $exam,
            'certificate' => $certificate,
            'chapters' => $course->chapters,
            'announsments' => $course->announcements,
            'lessions' => $course->lessons,
            'percents' => $inscription->percent,
            'present' => null,
        ]);

    }

    public function lesion($id)
    {

        $user = app('customer');
        $lessoning = CourseLesson::id($id);
        abort_unless($lessoning instanceof CourseLesson, 404);
        $course = $lessoning->course;

        $inscription = $this->resolveInscription($user, $course->id);

        // A3: bloquear acceso por URL si no respeta el orden del curso
        $this->assertLessonAccessible($lessoning, $inscription, $user->id);

        // C1/C2: precargar lecciones (con tipo) por capítulo en una sola query para evitar N+1 en el sidebar
        $chapters = $course->chapters()->with(['lessons' => function ($q) {
            $q->where('available', 1)->orderBy('position')->with('type');
        }])->get();
        $lessons = $course->lessons;
        $progress = $inscription->progress;
        $progres = $inscription->percent;
        $exam = $inscription->exam;
        $present = null;
        $certificate = null;

        if ($exam != null && $exam->score >= $this->passingScoreFor($exam)) {
            $inscription->certificate != null ? $certificate = $inscription->certificate : $certificate = null;
        }

        // C1: set de lecciones culminadas (evita N+1 de CourseProgress::validate en la vista)
        $completedLessonIds = $inscription->progress()
            ->where('culminated', 1)
            ->pluck('lesson_id')
            ->all();

        // C2: progreso por capítulo en una sola query (evita count() por capítulo en la vista)
        $chapterProgress = $inscription->progress()
            ->selectRaw('chapter_id, count(*) as total')
            ->groupBy('chapter_id')
            ->pluck('total', 'chapter_id')
            ->all();

        return view('customers.views.courses.lesion')->with([
            'course' => $course,
            'classing' => $lessoning,
            'class' => $lessons,
            'chapters' => $chapters,
            'inscription' => $inscription,
            'user' => $user,
            'progress' => $progress,
            'percents' => $progres,
            'present' => $present,
            'certificate' => $certificate,
            'action' => $progress,
            'exam' => $exam,
            'completedLessonIds' => $completedLessonIds,
            'chapterProgress' => $chapterProgress,

        ]);

    }

    public function player($lessonId)
    {
        $user = app('customer');
        $lesson = CourseLesson::findOrFail($lessonId);

        // Verificar que el usuario tiene inscripción activa al curso de esta lección
        $inscription = Inscription::where('user_id', $user->id)
            ->where('course_id', $lesson->course_id)
            ->first();

        if (! $inscription) {
            abort(403);
        }

        $rawUrl = $lesson->url ?? '';
        $platform = $lesson->platform;

        if (! $platform) {
            if (str_contains($rawUrl, 'youtube.com') || str_contains($rawUrl, 'youtu.be')) {
                $platform = 'youtube';
            } elseif (str_contains($rawUrl, 'vimeo.com')) {
                $platform = 'vimeo';
            } elseif (ctype_digit(trim($rawUrl))) {
                // Lecciones legacy: la url es solo el ID numérico de Vimeo
                $platform = 'vimeo';
            }
        }

        $embedUrl = null;
        if ($platform === 'youtube') {
            preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([\w\-]+)/', $rawUrl, $m);
            $videoId = $m[1] ?? $rawUrl;
            $embedUrl = 'https://www.youtube.com/embed/'.$videoId.'?rel=0&modestbranding=1';
        } elseif ($platform === 'vimeo') {
            preg_match('/vimeo\.com\/(?:video\/)?(\d+)/', $rawUrl, $m);
            $videoId = $m[1] ?? $rawUrl;
            $embedUrl = 'https://player.vimeo.com/video/'.$videoId;
        }

        if (! $embedUrl) {
            abort(404);
        }

        return response()->view('customers.views.courses.player', compact('embedUrl'))
            ->header('X-Frame-Options', 'SAMEORIGIN')
            ->header('Content-Security-Policy', "frame-ancestors 'self'");
    }

    public function realized(Request $request)
    {

        $user = app('customer');
        $lesson = CourseLesson::findOrFail($request->lesson);
        $inscription = $this->resolveInscription($user, $lesson->course_id);
        $course = $inscription->course;

        // C3: calcular una sola vez; recontar progreso solo tras crear el avance
        $totalLessons = $course->lessons()->count();

        $alreadyCompleted = $inscription->progress()->where('lesson_id', $lesson->id)->exists();

        if (! $alreadyCompleted) {
            $this->createOrUpdateProgress($user, $course, $lesson, $inscription);
        }

        $completedLessons = $inscription->progress()->count();

        if ($completedLessons >= $totalLessons) {
            return $this->handleCourseCompletion($inscription);
        }

        $nextLesson = CourseProgress::prevNext($lesson->id, 'next');

        if ($nextLesson !== 'true') {
            return $this->redirectToNextLesson($nextLesson);
        }

        return redirect()->route('customers.courses.content', $inscription->slack);
    }

    private function redirectToNextLesson($nextLesson)
    {

        if (! $nextLesson) {
            return redirect()->route('customers.courses.content');
        }

        if (! isset($nextLesson->type_id) || ! is_numeric($nextLesson->type_id)) {
            return redirect()->back()->with('error', 'Tipo de lección inválido');
        }

        $route = $nextLesson->type_id == 6 ? 'customers.courses.quiz' : 'customers.courses.lesion';

        return redirect()->route($route, $nextLesson->id);

    }

    public function prev(Request $request)
    {

        $user = app('customer');
        $lessoning = CourseLesson::id($request->lesson);
        $inscription = $this->resolveInscription($user, $lessoning->course_id);
        $nextLesson = CourseProgress::prevNext($lessoning->id, 'prev');

        if ($nextLesson != 'true') {
            if ($nextLesson->type_id == 6) {
                return redirect()->route('customers.courses.quiz', $nextLesson->id);
            } else {
                return redirect()->route('customers.courses.lesion', $nextLesson->id);
            }
        } else {
            return redirect()->route('customers.courses.content', $inscription->slack);
        }

    }

    public function review(Request $request)
    {
        // Las reseñas solo se aceptan si el administrador habilitó la opción en el panel.
        if (! setting('reviews_enabled')) {
            return back()->with('error', 'Las reseñas no están habilitadas en este momento.');
        }

        $user = app('customer');

        $request->validate([
            'course' => 'required',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ], [
            'rating.required' => 'Selecciona una calificación.',
            'rating.min' => 'La calificación debe ser de 1 a 5 estrellas.',
            'rating.max' => 'La calificación debe ser de 1 a 5 estrellas.',
        ]);

        $course = Course::where('slack', $request->course)->first();

        if (! $course) {
            return back()->with('error', 'Curso no encontrado.');
        }

        // Solo quien esté inscrito puede calificar el curso.
        $inscription = Inscription::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if (! $inscription) {
            return back()->with('error', 'Debes estar inscrito en el curso para calificarlo.');
        }

        // Una reseña por usuario y curso (se puede actualizar la propia).
        $review = CourseReview::firstOrNew([
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);

        if (! $review->exists) {
            $review->slack = $this->generate_slack('course_reviews');
        }

        $review->inscription_id = $inscription->id;
        $review->rating = (int) $request->rating;
        $review->comment = $request->comment;
        $review->save();

        // Actualizar el promedio del curso a partir de todas sus reseñas.
        $course->recalculateRating();

        return back()->with('success', '¡Gracias por calificar este curso!');
    }

    private function createExamIfNeeded($inscription, $course)
    {

        $topic = $course->examtopic->first();

        if (! $topic) {
            return null;
        }

        $existingExam = Exam::where('inscription_id', $inscription->id)->first();

        if ($existingExam) {
            return $existingExam;
        } else {
            return Exam::create([
                'inscription_id' => $inscription->id,
                'topic_id' => $topic->id,
                'course_id' => $course->id,
                'user_id' => $inscription->user_id,
            ]);
        }

    }

    private function createOrUpdateProgress($user, $course, $lesson, $inscription)
    {

        $progress = $inscription->progress()->where('lesson_id', $lesson->id)->first();

        if (! $progress) {

            // B6: avance + recálculo de porcentaje en una sola transacción
            DB::transaction(function () use ($user, $course, $lesson, $inscription) {
                CourseProgress::create([
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                    'chapter_id' => $lesson->chapter_id,
                    'inscription_id' => $inscription->id,
                    'lesson_id' => $lesson->id,
                    'culminated' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $totalLessons = $course->lessons()->count();
                $completedLessons = $inscription->progress()->count();
                $percent = $totalLessons > 0
                    ? min(100, round(($completedLessons / $totalLessons) * 100, 2))
                    : 100;

                $inscription->update(['percent' => $percent]);
            });
        }

    }

    private function handleCourseCompletion($inscription)
    {

        $exam = $this->createExamIfNeeded($inscription, $inscription->course);

        if ($exam && $exam->score < $this->passingScoreFor($exam)) {
            return redirect()->route('customers.courses.exam', $inscription->course->slack);
        }

        return redirect()->route('customers.courses.content', $inscription->slack);
    }
}
