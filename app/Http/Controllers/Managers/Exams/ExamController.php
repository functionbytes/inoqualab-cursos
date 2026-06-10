<?php

namespace App\Http\Controllers\Managers\Exams;

use App\Http\Controllers\Controller;
use App\Models\Course\Course;
use App\Models\Exam\ExamTopic;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExamController extends Controller
{
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
        ]);
    }

    public function create($slack)
    {
        $course = Course::slack($slack);

        $availables = $this->availableOptions();

        $types = collect([
            ['id' => '1', 'label' => 'Selección Multiple'],
            ['id' => '0', 'label' => 'Falso - Verdadero'],
        ]);

        $types = $types->pluck('label', 'id');

        return view('managers.views.exams.exams.create')->with([
            'course' => $course,
            'availables' => $availables,
            'types' => $types,
        ]);

    }

    public function store(Request $request)
    {

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

    public function edit($slack)
    {
        $topic = ExamTopic::slack($slack);
        $course = $topic->course;

        $availables = $this->availableOptions();

        $types = collect([
            ['id' => '1', 'label' => 'Selección Multiple'],
            ['id' => '0', 'label' => 'Falso - Verdadero'],
        ]);

        $types = $types->pluck('label', 'id');

        return view('managers.views.exams.exams.edit')->with([
            'topic' => $topic,
            'course' => $course,
            'availables' => $availables,
            'types' => $types,
        ]);
    }

    public function update(Request $request)
    {

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
        $topic = ExamTopic::slack($slack);
        $topic->questions()->delete();
        $topic->delete();

        return back();
    }
}
