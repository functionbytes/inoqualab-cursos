<?php

namespace App\Http\Controllers\Supports\Users;

use App\Exports\Managers\ResultsExport;
use App\Http\Controllers\Controller;
use App\Models\Course\Course;
use App\Models\User;
use App\Models\Users\Certificate;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ResultsController extends Controller
{
    public function index(Request $request, $slack)
    {

        $searchKey = $request->search;
        $course = $request->course;
        $year = $request->year;

        $courses = Course::latest()->get();
        $user = User::slack($slack);
        $certificates = $user->certificates()->latest();

        $years = $user->certificates()
            ->selectRaw('YEAR(start_at) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        if ($searchKey) {
            $certificates = $certificates->join('courses', 'certificates.course_id', '=', 'courses.id')
                ->where('courses.title', 'like', '%'.$searchKey.'%')
                ->select('certificates.*');
        }

        if ($course != null) {
            $certificates = $certificates->where('course_id', $course);
        }

        if ($year != null) {
            $certificates = $certificates->whereYear('start_at', $year);
        }

        $certificates = $certificates->paginate(paginationNumber());

        return view('supports.views.enterprises.users.results.index')->with([
            'certificates' => $certificates,
            'searchKey' => $searchKey,
            'courses' => $courses,
            'course' => $course,
            'years' => $years,
            'year' => $year,
            'user' => $user,
        ]);
    }

    public function view($slack)
    {

        $certificate = Certificate::slack($slack);
        $course = $certificate->course;
        $exam = $certificate->exam;
        $answers = $certificate->exam?->answers;
        $wrongs = $certificate->exam?->answers()?->wrong()->count();
        $corrects = $certificate->exam?->answers()?->correct()->count();

        return view('supports.views.enterprises.users.results.view')->with([
            'course' => $course,
            'certificate' => $certificate,
            'exam' => $exam,
            'answers' => $answers,
            'corrects' => $corrects,
            'wrongs' => $wrongs,
        ]);

    }

    public function download($slack)
    {

        $certificate = Certificate::slack($slack);
        $course = $certificate->course;
        $exam = $certificate->exam;
        $user = $certificate->user;

        return Excel::download(new ResultsExport($exam), $course->title.' - '.$user->identification.'.xlsx');

    }
}
