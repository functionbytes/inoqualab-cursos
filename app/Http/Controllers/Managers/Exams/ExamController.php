<?php

namespace App\Http\Controllers\Managers\Exams;

use App\Http\Controllers\Concerns\BuildsAssessmentForms;
use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Exams\StoreExamRequest;
use App\Http\Requests\Managers\Exams\UpdateExamRequest;
use App\Models\Course\Course;
use App\Models\Exam\ExamTopic;
use Illuminate\Http\Request;
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
        $topic->questions()->delete();
        $topic->delete();

        return back();
    }
}
