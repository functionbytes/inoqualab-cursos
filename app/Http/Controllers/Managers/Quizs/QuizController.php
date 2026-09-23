<?php

namespace App\Http\Controllers\Managers\Quizs;

use App\Http\Controllers\Concerns\BuildsAssessmentForms;
use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Quizs\BulkActionQuizRequest;
use App\Http\Requests\Managers\Quizs\StoreQuizRequest;
use App\Http\Requests\Managers\Quizs\UpdateQuizRequest;
use App\Models\Course\Course;
use App\Models\Quiz\Quiz;
use App\Models\Quiz\QuizTopic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    use BuildsAssessmentForms;

    public function index(Request $request, $slack)
    {

        $course = Course::slack($slack);
        $searchKey = $request->search;
        $available = $request->available;
        $lesson = $request->lesson;

        $lessons = $course->lessons()->quizzes()->get(['id', 'title']);
        $quizs = QuizTopic::query()->where('course_id', $course->id);

        if ($searchKey) {
            $quizs = $quizs->where('title', 'like', '%'.$searchKey.'%');
        }

        if ($request->available != null) {
            $quizs = $quizs->where('available', $available);
        }

        if ($request->lesson != null) {
            $quizs = $quizs->where('lesson_id', $lesson);
        }

        $quizs = $quizs->paginate(paginationNumber());

        // Solo lecciones de tipo Quiz (type_id 6, ver CourseLesson::scopeQuizzes):
        // el estudiante ve el quiz a traves de CourseLesson::quiztopic() al abrir
        // ESA leccion especifica. Asociar un quiz a una leccion de otro tipo
        // (texto/video) lo dejaba guardado pero invisible, sin ningun error.
        $lessonOptions = $course->lessons()->quizzes()->get()->prepend('', '')->pluck('title', 'id');

        $view = request()->ajax() ? 'managers.views.quizs.quizs._table' : 'managers.views.quizs.quizs.index';

        return view($view)->with([
            'course' => $course,
            'quizs' => $quizs,
            'lessons' => $lessons,
            'lesson' => $lesson,
            'available' => $available,
            'searchKey' => $searchKey,
            'availables' => $this->availableOptions(true),
            'types' => $this->assessmentTypes(withBlank: true),
            'lessonOptions' => $lessonOptions,
        ]);
    }

    public function store(StoreQuizRequest $request)
    {
        abort_unless(auth()->user()->can('quizzes.create'), 403);

        $course = Course::slack($request->course);
        abort_unless($course instanceof Course, 404, 'El curso indicado no existe.');

        $topic = new QuizTopic;
        $topic->slack = $this->generate_slack('quiz_topics');
        $topic->title = $request->title;
        $topic->description = $request->description;
        $topic->timer = $request->timer;
        $topic->per_q_mark = $request->mark;
        $topic->show_ans = $request->question;
        $topic->quiz_again = $request->duration;
        $topic->due_days = $request->day;
        $topic->lesson_id = $request->lesson;
        $topic->available = $request->available;
        $topic->type = $request->type;
        $topic->course_id = $course->id;
        $topic->save();

        return response()->json([

            'success' => true,
            'message' => 'Se ha creado correctamente',
        ]);

    }

    /**
     * Datos del quiz para el modal de edición (fetch AJAX desde el listado).
     */
    public function edit($slack)
    {
        $topic = QuizTopic::slack($slack);
        abort_unless($topic instanceof QuizTopic, 404);

        return response()->json([
            'slack' => $topic->slack,
            'title' => $topic->title,
            'lesson_id' => $topic->lesson_id,
            'type' => (int) $topic->type,
            'duration' => (int) $topic->quiz_again,
            'day' => $topic->due_days,
            'timer' => $topic->timer,
            'question' => $topic->show_ans,
            'mark' => $topic->per_q_mark,
            'available' => (int) $topic->available,
            'description' => $topic->description,
        ]);
    }

    public function update(UpdateQuizRequest $request)
    {
        abort_unless(auth()->user()->can('quizzes.update'), 403);

        $topic = QuizTopic::slack($request->slack);
        abort_unless($topic instanceof QuizTopic, 404);

        $topic->title = $request->title;
        $topic->description = $request->description;
        $topic->timer = $request->timer;
        $topic->per_q_mark = $request->mark;
        $topic->show_ans = $request->question;
        $topic->quiz_again = $request->duration;
        $topic->due_days = $request->day;
        $topic->lesson_id = $request->lesson;
        $topic->available = $request->available;
        $topic->type = $request->type;
        $topic->update();

        return response()->json([
            'success' => true,
            'message' => 'Se ha actualizado  correctamente',
        ]);

    }

    public function destroy($slack)
    {
        abort_unless(auth()->user()->can('quizzes.delete'), 403);

        $topic = QuizTopic::slack($slack);
        abort_unless($topic instanceof QuizTopic, 404);

        // Bloquear el borrado de un quiz que ya resolvieron alumnos: soft-borrar el
        // topic y sus preguntas dejaría los intentos históricos sin sus preguntas.
        if (Quiz::where('topic_id', $topic->id)->exists()) {
            return back()->with('error', 'No se puede eliminar: hay alumnos que ya resolvieron este quiz; se perderían los resultados de sus intentos.');
        }

        DB::transaction(function () use ($topic) {
            $topic->questions()->delete();
            $topic->delete();
        });

        return back()->with('success', 'Quiz eliminado correctamente.');

    }

    public function bulkAction(BulkActionQuizRequest $request): JsonResponse
    {
        if ($request->action === 'delete') {
            $topics = QuizTopic::whereIn('id', $request->ids)->get();

            // Mismo resguardo que destroy(): un quiz que ya resolvieron alumnos
            // no se elimina (se perderían los resultados de sus intentos).
            $attemptedTopicIds = Quiz::whereIn('topic_id', $topics->pluck('id'))
                ->distinct()->pluck('topic_id');
            $deletable = $topics->reject(fn ($topic) => $attemptedTopicIds->contains($topic->id));
            $count = $deletable->count();

            DB::transaction(function () use ($deletable) {
                foreach ($deletable as $topic) {
                    $topic->questions()->delete();
                    $topic->delete();
                }
            });

            return response()->json(['success' => true, 'message' => $count.' quiz(zes) procesados.']);
        }

        $query = QuizTopic::whereIn('id', $request->ids);
        $count = $query->count();

        match ($request->action) {
            'publish' => $query->update(['available' => 1]),
            'hide' => $query->update(['available' => 0]),
        };

        return response()->json(['success' => true, 'message' => $count.' quiz(zes) procesados.']);
    }
}
