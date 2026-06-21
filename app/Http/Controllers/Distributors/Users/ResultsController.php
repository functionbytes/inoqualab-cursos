<?php

namespace App\Http\Controllers\Distributors\Users;

use App\Exports\Distributors\ResultsExport;
use App\Http\Controllers\Controller;
use App\Models\Course\Course;
use App\Models\Enterprise\EnterpriseUser;
use App\Models\User;
use App\Models\Users\Certificate;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ResultsController extends Controller
{
    /** IDs de las empresas del distribuidor autenticado. */
    private function distributorEnterpriseIds(): array
    {
        return app('distributor')->enterprises()->pluck('enterprises.id')->all();
    }

    /** Usuario que pertenece (enterprise_user) a una empresa del distribuidor, o 404. */
    private function managedUser(string $slack): User
    {
        $enterpriseIds = $this->distributorEnterpriseIds();

        return User::where('slack', $slack)
            ->whereExists(fn ($q) => $q->selectRaw('1')->from('enterprise_user')
                ->whereColumn('enterprise_user.user_id', 'users.id')
                ->whereIn('enterprise_user.enterprise_id', $enterpriseIds))
            ->firstOrFail();
    }

    /** Aborta 404 si el user_id no pertenece a una empresa del distribuidor. */
    private function assertManagedUser($userId): void
    {
        abort_unless(
            EnterpriseUser::where('user_id', $userId)
                ->whereIn('enterprise_id', $this->distributorEnterpriseIds())
                ->exists(),
            404
        );
    }

    public function index(Request $request, $slack)
    {

        $searchKey = $request->search;
        $course = $request->course;
        $year = $request->year;

        $courses = Course::latest()->get();
        $user = $this->managedUser($slack);
        $certificates = $user->certificates()->with('course')->latest();

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

        return view('distributors.views.enterprises.users.results.index')->with([
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
        $this->assertManagedUser($certificate->user_id);
        $course = $certificate->course;
        $exam = $certificate->exam;
        $answers = $certificate->exam?->answers;
        $wrongs = $certificate->exam?->answers()?->wrong()->count();
        $corrects = $certificate->exam?->answers()?->correct()->count();

        return view('distributors.views.enterprises.users.results.view')->with([
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
        $this->assertManagedUser($certificate->user_id);
        $course = $certificate->course;
        $exam = $certificate->exam;
        $user = $certificate->user;

        return Excel::download(new ResultsExport($exam), $course->title.' - '.$user->identification.'.xlsx');

    }
}
