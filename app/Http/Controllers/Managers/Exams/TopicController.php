<?php

namespace App\Http\Controllers\Managers\Exams;

use App\Http\Controllers\Controller;
use App\Models\Exam\ExamQuestion;
use App\Models\Exam\ExamTopic;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    public function index(Request $request, $slack)
    {

        $topic = ExamTopic::slack($slack);
        $course = $topic->course;

        $searchKey = $request->search;
        $available = $request->available;

        $questions = $topic->questions();

        if ($searchKey) {
            $questions = $questions->where('title', 'like', '%'.$searchKey.'%');
        }

        if ($request->available != null) {
            $questions = $questions->where('available', $available);
        }

        $questions = $questions->paginate(paginationNumber());

        return view('managers.views.exams.topics.index')->with([
            'course' => $course,
            'topic' => $topic,
            'questions' => $questions,
            'available' => $available,
            'searchKey' => $searchKey,
        ]);
    }

    public function create($slack)
    {

        $topic = ExamTopic::slack($slack);
        $course = $topic->course;

        $availables = $this->availableOptions();

        $answers = collect([]);

        if ($topic->type == 0) {

            $answers = collect([
                ['id' => 'true', 'title' => 'Verdadero'],
                ['id' => 'false', 'title' => 'Falso'],
            ]);

            $answers = $answers->pluck('title', 'id');
        }

        if ($topic->type == 1) {

            $answers = collect([
                ['id' => 'a', 'title' => 'A'],
                ['id' => 'b', 'title' => 'B'],
                ['id' => 'c', 'title' => 'C'],
                ['id' => 'd', 'title' => 'D'],
            ]);

            $answers = $answers->pluck('title', 'id');
        }

        return view('managers.views.exams.topics.create')->with([
            'course' => $course,
            'topic' => $topic,
            'availables' => $availables,
            'answers' => $answers,
        ]);

    }

    public function store(Request $request)
    {

        $topic = ExamTopic::slack($request->topic);
        $course = $topic->course;

        $question = new ExamQuestion;
        $question->slack = $this->generate_slack('exam_questions');
        $question->question = $request->question;
        $question->course_id = $course->id;
        $question->topic_id = $topic->id;
        $question->type = $topic->type;
        $question->available = $request->available;

        if ($topic->type == 1) {
            $question->a = $request->a;
            $question->b = $request->b;
            $question->c = $request->c;
            $question->d = $request->d;
            $question->answer = $request->answer;
        } else {
            $question->answer = $request->answer;
        }

        $question->save();

        return response()->json([
            'success' => true,
            'message' => 'Se ha creado correctamente',
            'slack' => $question->slack,
        ]);

    }

    public function edit($slack)
    {

        $question = ExamQuestion::slack($slack);
        $topic = $question->topic;
        $course = $question->course;

        $availables = $this->availableOptions();

        $answers = collect([]);

        if ($question->type == 0) {

            $answers = collect([
                ['id' => 'true', 'title' => 'Verdadero'],
                ['id' => 'false', 'title' => 'Falso'],
            ]);

            $answers = $answers->pluck('title', 'id');
        }

        if ($question->type == 1) {

            $answers = collect([
                ['id' => 'a', 'title' => 'A'],
                ['id' => 'b', 'title' => 'B'],
                ['id' => 'c', 'title' => 'C'],
                ['id' => 'd', 'title' => 'D'],
            ]);

            $answers = $answers->pluck('title', 'id');

        }

        return view('managers.views.exams.topics.edit')->with([
            'topic' => $topic,
            'question' => $question,
            'availables' => $availables,
            'course' => $course,
            'answers' => $answers,
        ]);

    }

    public function update(Request $request)
    {

        $question = ExamQuestion::slack($request->slack);
        $topic = $question->topic;
        $question->question = $request->question;
        $question->answer = $request->answer;
        $question->available = $request->available;

        if ($topic->type == 1) {
            $question->a = $request->a;
            $question->b = $request->b;
            $question->c = $request->c;
            $question->d = $request->d;
            $question->answer = $request->answer;
        } else {
            $question->answer = $request->answer;
        }

        $question->update();

        return response()->json([
            'success' => true,
            'message' => 'Se ha actualizado correctamente',
            'slack' => $question->slack,
        ]);

    }

    public function destroy($slack)
    {
        $question = ExamQuestion::slack($slack);
        $question->delete();

        return back();
    }
}
