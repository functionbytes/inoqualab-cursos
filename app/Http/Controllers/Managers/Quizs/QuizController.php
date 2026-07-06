<?php

namespace App\Http\Controllers\Managers\Quizs;

use App\Http\Controllers\Concerns\BuildsAssessmentForms;
use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Quizs\StoreQuizRequest;
use App\Http\Requests\Managers\Quizs\UpdateQuizRequest;
use App\Models\Course\Course;
use App\Models\Quiz\QuizTopic;
use Illuminate\Http\Request;

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

        $lessonOptions = $course->lessons()->get()->prepend('', '')->pluck('title', 'id');

        return view('managers.views.quizs.quizs.index')->with([
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
        $topic->questions()->delete();
        $topic->delete();

        return back();

    }
}
