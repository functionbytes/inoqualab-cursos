<?php

namespace App\Http\Controllers\Enterprises\Users;

use App\Exports\Enterprises\ResultsExport;
use App\Http\Controllers\Controller;
use App\Models\Course\Course;
use App\Models\User;
use App\Models\Users\Certificate;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ResultsController extends Controller
{
    /** Usuario que pertenece a la empresa autenticada, o 404 (evita IDOR). */
    private function managedUser(string $slack): User
    {
        return app('enterprise')->users()->where('users.slack', $slack)->firstOrFail();
    }

    /** Aborta 404 si el user_id no pertenece a la empresa autenticada. */
    private function assertEnterpriseUser($userId): void
    {
        abort_unless(app('enterprise')->users()->where('users.id', $userId)->exists(), 404);
    }

    public function index(Request $request, $slack)
    {

        $searchKey = $request->search;
        $course = $request->course;
        $year = $request->year;

        $courses = Course::latest()->get();
        $user = $this->managedUser($slack);

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

        $years = $user->certificates()
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
        $this->assertEnterpriseUser($certificate->user_id);
        $exam = $certificate->exam;
        $answers = $certificate->exam?->answers()->with('question')->get();
        $wrongs = $answers?->where('approved', 0)->count();
        $corrects = $answers?->where('approved', 1)->count();

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
        $this->assertEnterpriseUser($certificate->user_id);
        $exam = $certificate->exam;
        $user = $certificate->user;

        return Excel::download(new ResultsExport($exam), $user->identification.'.xlsx');

    }
}
