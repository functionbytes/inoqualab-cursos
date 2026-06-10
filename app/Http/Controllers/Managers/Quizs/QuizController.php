<?php

namespace App\Http\Controllers\Managers\Quizs;

use App\Http\Controllers\Controller;
use App\Models\Course\Course;
use App\Models\Quiz\QuizTopic;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function index(Request $request, $slack)
    {

        $course = Course::slack($slack);
        $searchKey = $request->search;
        $available = $request->available;
        $lesson = $request->lesson;

        $lessons = $course->lessons()->quizzes()->get();
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

        return view('managers.views.quizs.quizs.index')->with([
            'course' => $course,
            'quizs' => $quizs,
            'lessons' => $lessons,
            'lesson' => $lesson,
            'available' => $available,
            'searchKey' => $searchKey,
        ]);
    }

    public function create($slack)
    {

        $course = Course::slack($slack);
        $lessons = $course->lessons()->get();
        $lessons->prepend('', '');
        $lessons = $lessons->pluck('title', 'id');

        $availables = $this->availableOptions(true);

        $types = collect([
            ['id' => '1', 'label' => 'SELECCIÓN MULTIPLE'],
            ['id' => '0', 'label' => 'FALSO O VERDADERO'],
        ]);

        $types->prepend('', '');
        $types = $types->pluck('label', 'id');

        return view('managers.views.quizs.quizs.create')->with([
            'course' => $course,
            'availables' => $availables,
            'lessons' => $lessons,
            'types' => $types,
        ]);

    }

    public function store(Request $request)
    {

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

    public function edit($slack)
    {

        $topic = QuizTopic::slack($slack);
        $course = $topic->course;

        $class = $course->lessons()->quizzes()->get();
        $class = $class->pluck('title', 'id');

        $availables = $this->availableOptions();

        $types = collect([
            ['id' => '1', 'label' => 'Selección Multiple'],
            ['id' => '0', 'label' => 'Falso - Verdadero'],
        ]);

        $types = $types->pluck('label', 'id');

        return view('managers.views.quizs.quizs.edit')->with([
            'topic' => $topic,
            'course' => $course,
            'class' => $class,
            'availables' => $availables,
            'types' => $types,
        ]);

    }

    public function update(Request $request)
    {

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

        $topic = QuizTopic::slack($slack);
        $topic->questions()->delete();
        $topic->delete();

        return back();

    }
}
