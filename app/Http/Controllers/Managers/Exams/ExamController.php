<?php

namespace App\Http\Controllers\Managers\Exams;

use App\Http\Controllers\Concerns\BuildsAssessmentForms;
use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Exams\StoreExamRequest;
use App\Http\Requests\Managers\Exams\UpdateExamRequest;
use App\Models\Course\Course;
use App\Models\Exam\Exam;
use App\Models\Exam\ExamTopic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExamController extends Controller
{
    use BuildsAssessmentForms;

    public function index(Request $request, $slack)
    {

        $course = Course::slack($slack);

        $searchKey = $request->search;
        $available = $request->available;

        $exams = ExamTopic::query()->where('course_id', $course->id);

        if ($searchKey) {
            $exams = $exams->where('title', 'like', '%'.$searchKey.'%');
        }

        if ($request->available != null) {
            $exams = $exams->where('available', $available);
        }

        $exams = $exams->paginate(paginationNumber());

        return view('managers.views.exams.exams.index')->with([
            'course' => $course,
            'exams' => $exams,
            'available' => $available,
            'searchKey' => $searchKey,
            'availables' => $this->availableOptions(true),
            'types' => $this->assessmentTypes(withBlank: true),
        ]);
    }

    public function store(StoreExamRequest $request)
    {
        abort_unless(auth()->user()->can('exams.create'), 403);

        $course = Course::slack($request->course);
        abort_unless($course instanceof Course, 404, 'El curso indicado no existe.');

        $topic = new ExamTopic;
        $topic->slack = $this->generate_slack('exam_topics');
        $topic->title = Str::upper($request->title);
        $topic->description = $request->description;
        $topic->timer = $request->timer;
        $topic->per_q_mark = $request->mark;
        $topic->show_ans = $request->question;
        $topic->quiz_again = $request->duration;
        $topic->due_days = $request->day;
        $topic->available = $request->available;
        $topic->type = $request->type;
        $topic->course_id = $course->id;
        $topic->save();

        return response()->json([
            'success' => true,
            'message' => 'Se ha creado correctamente',
            'slack' => $topic->slack,
        ]);

    }

    /**
     * Datos del examen para el modal de edición (fetch AJAX desde el listado).
     */
    public function edit($slack)
    {
        $topic = ExamTopic::slack($slack);
        abort_unless($topic instanceof ExamTopic, 404);

        return response()->json([
            'slack' => $topic->slack,
            'title' => $topic->title,
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

    public function update(UpdateExamRequest $request)
    {
        abort_unless(auth()->user()->can('exams.update'), 403);

        $topic = ExamTopic::slack($request->slack);
        abort_unless($topic instanceof ExamTopic, 404);

        $topic->title = $request->title;
        $topic->description = $request->description;
        $topic->timer = $request->timer;
        $topic->per_q_mark = $request->mark;
        $topic->show_ans = $request->question;
        $topic->quiz_again = $request->duration;
        $topic->due_days = $request->day;
        $topic->available = $request->available;
        $topic->type = $request->type;
        $topic->update();

        return response()->json([
            'success' => true,
            'message' => 'Se ha actualizado correctamente',
            'slack' => $topic->slack,
        ]);

    }

    public function destroy($slack)
    {
        abort_unless(auth()->user()->can('exams.delete'), 403);
        $topic = ExamTopic::slack($slack);
        abort_unless($topic instanceof ExamTopic, 404);

        // Bloquear el borrado de un examen que ya rindieron alumnos: soft-borrar el
        // topic y sus preguntas dejaría los intentos históricos (exam_answers) sin
        // sus preguntas, rompiendo la visualización de resultados.
        if (Exam::where('topic_id', $topic->id)->exists()) {
            return back()->with('error', 'No se puede eliminar: hay alumnos que ya rindieron este examen; se perderían los resultados de sus intentos.');
        }

        DB::transaction(function () use ($topic) {
            $topic->questions()->delete();
            $topic->delete();
        });

        return back()->with('success', 'Examen eliminado correctamente.');
    }
}
