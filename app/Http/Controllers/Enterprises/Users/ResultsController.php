<?php

namespace App\Http\Controllers\Enterprises\Users;

use App\Exports\Enterprises\ResultsExport;
use App\Http\Controllers\Controller;
use App\Models\Course\Course;
use App\Models\User;
use App\Models\Users\Certificate;
use DB;
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

        $certificatesQuery = $user->certificates()
            ->join('courses', 'certificates.course_id', '=', 'courses.id')
            ->select('certificates.*', 'courses.title')
            ->latest();

        if (! empty($searchKey)) {
            $certificatesQuery->where('courses.title', 'like', '%'.$searchKey.'%');
        }

        if (! empty($course)) {
            $certificatesQuery->where('course_id', $course);
        }

        if (! empty($year)) {
            $certificatesQuery->whereYear('start_at', $year);
        }

        $certificates = $certificatesQuery->paginate(paginationNumber());

        $years = DB::table('certificates')
            ->where('certificates.user_id', $user->id)
            ->selectRaw('YEAR(start_at) as year')
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->pluck('year');

        return view('enterprises.views.users.results.index')->with([
            'certificates' => $certificates,
            'searchKey' => $searchKey,
            'courses' => $courses,
            'course' => $course,
            'years' => $years,
            'year' => $year,
        ]);

    }

    public function view($slack)
    {

        $certificate = Certificate::slack($slack);
        $exam = $certificate->exam;
        $answers = $certificate->exam?->answers;
        $wrongs = $certificate->exam?->answers()?->wrong()->count();
        $corrects = $certificate->exam?->answers()?->correct()->count();

        return view('enterprises.views.users.results.view')->with([
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
        $exam = $certificate->exam;
        $user = $certificate->user;

        return Excel::download(new ResultsExport($exam), $user->identification.'.xlsx');

    }
}
