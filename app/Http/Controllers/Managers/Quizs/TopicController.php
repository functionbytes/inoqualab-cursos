<?php

namespace App\Http\Controllers\Managers\Quizs;

use App\Http\Controllers\Concerns\BuildsAssessmentForms;
use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Quizs\StoreQuizQuestionRequest;
use App\Http\Requests\Managers\Quizs\UpdateQuizQuestionRequest;
use App\Models\Quiz\QuizQuestion;
use App\Models\Quiz\QuizTopic;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    use BuildsAssessmentForms;

    public function index(Request $request, $slack)
    {

        $topic = QuizTopic::slack($slack);
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

        return view('managers.views.quizs.topics.index')->with([
            'course' => $course,
            'topic' => $topic,
            'questions' => $questions,
            'available' => $available,
            'searchKey' => $searchKey,
            'availables' => $this->availableOptions(),
            'answers' => $this->assessmentAnswers($topic->type),
        ]);

    }

    public function store(StoreQuizQuestionRequest $request)
    {
        abort_unless(auth()->user()->can('quizzes.create'), 403);

        $topic = QuizTopic::slack($request->topic);
        $question = new QuizQuestion;
        $question->slack = $this->generate_slack('quiz_questions');
        $question->question = $request->question;
        $question->topic_id = $topic->id;
        $question->lesson_id = $topic->lesson_id;
        $question->type = $topic->type;
        $question->available = $request->available;
        $this->applyAnswerFields($question, $topic->type, $request);

        $question->save();

        return response()->json([
            'success' => true,
            'message' => 'Se ha creado correctamente',
        ]);

    }

    /**
     * Datos de la pregunta para el modal de edición (fetch AJAX desde el listado).
     */
    public function edit($slack)
    {
        $question = QuizQuestion::slack($slack);

        return response()->json([
            'slack' => $question->slack,
            'question' => $question->question,
            'available' => (int) $question->available,
            'answer' => $question->answer,
            'a' => $question->a,
            'b' => $question->b,
            'c' => $question->c,
            'd' => $question->d,
        ]);
    }

    public function update(UpdateQuizQuestionRequest $request)
    {
        abort_unless(auth()->user()->can('quizzes.update'), 403);

        $question = QuizQuestion::slack($request->slack);
        $topic = $question->topic;
        $question->question = $request->question;
        $question->available = $request->available;
        $this->applyAnswerFields($question, $topic->type, $request);

        $question->update();

        return response()->json([
            'success' => true,
            'message' => 'Se ha actualizado  correctamente',
        ]);

    }

    public function destroy($slack)
    {
        abort_unless(auth()->user()->can('quizzes.delete'), 403);

        $question = QuizQuestion::slack($slack);
        $question->delete();

        return back();

    }
}
