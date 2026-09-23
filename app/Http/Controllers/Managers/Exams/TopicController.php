<?php

namespace App\Http\Controllers\Managers\Exams;

use App\Http\Controllers\Concerns\BuildsAssessmentForms;
use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Exams\BulkActionExamQuestionRequest;
use App\Http\Requests\Managers\Exams\StoreExamQuestionRequest;
use App\Http\Requests\Managers\Exams\UpdateExamQuestionRequest;
use App\Models\Course\Course;
use App\Models\Exam\ExamQuestion;
use App\Models\Exam\ExamTopic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    use BuildsAssessmentForms;

    public function index(Request $request, $slack)
    {

        $topic = ExamTopic::slack($slack);

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

        $view = request()->ajax() ? 'managers.views.exams.topics._table' : 'managers.views.exams.topics.index';

        return view($view)->with([
            'topic' => $topic,
            'questions' => $questions,
            'available' => $available,
            'searchKey' => $searchKey,
            'availables' => $this->availableOptions(),
            'answers' => $this->assessmentAnswers($topic->type),
        ]);
    }

    public function store(StoreExamQuestionRequest $request)
    {
        abort_unless(auth()->user()->can('exams.create'), 403);

        $topic = ExamTopic::slack($request->topic);
        $course = $topic->course;
        // El curso puede haberse borrado (soft delete) después de crear el topic.
        abort_unless($course instanceof Course, 404, 'El curso de este topic ya no existe.');

        $question = new ExamQuestion;
        $question->slack = $this->generate_slack('exam_questions');
        $question->question = $request->question;
        $question->course_id = $course->id;
        $question->topic_id = $topic->id;
        $question->type = $topic->type;
        $question->available = $request->available;
        $this->applyAnswerFields($question, $topic->type, $request);

        $question->save();

        return response()->json([
            'success' => true,
            'message' => 'Se ha creado correctamente',
            'slack' => $question->slack,
        ]);

    }

    /**
     * Datos de la pregunta para el modal de edición (fetch AJAX desde el listado).
     */
    public function edit($slack)
    {
        $question = ExamQuestion::slack($slack);

        return response()->json([
            'slack' => $question->slack,
            'question' => $question->question,
            'available' => (int) $question->available,
            'answer' => $this->answerForEdit($question->answer),
            'a' => $question->a,
            'b' => $question->b,
            'c' => $question->c,
            'd' => $question->d,
        ]);
    }

    public function update(UpdateExamQuestionRequest $request)
    {
        abort_unless(auth()->user()->can('exams.update'), 403);

        $question = ExamQuestion::slack($request->slack);
        $topic = $question->topic;
        $question->question = $request->question;
        $question->available = $request->available;
        $this->applyAnswerFields($question, $topic->type, $request);

        $question->update();

        return response()->json([
            'success' => true,
            'message' => 'Se ha actualizado correctamente',
            'slack' => $question->slack,
        ]);

    }

    public function destroy($slack)
    {
        abort_unless(auth()->user()->can('exams.delete'), 403);
        $question = ExamQuestion::slack($slack);
        $question->delete();

        return back();
    }

    public function bulkAction(BulkActionExamQuestionRequest $request): JsonResponse
    {
        $query = ExamQuestion::whereIn('id', $request->ids);
        $count = $query->count();

        match ($request->action) {
            'publish' => $query->update(['available' => 1]),
            'hide' => $query->update(['available' => 0]),
            'delete' => $query->delete(),
        };

        return response()->json(['success' => true, 'message' => $count.' pregunta(s) procesados.']);
    }
}
