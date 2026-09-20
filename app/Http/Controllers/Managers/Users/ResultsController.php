<?php

namespace App\Http\Controllers\Managers\Users;

use App\Exports\Managers\ResultsExport;
use App\Http\Controllers\Controller;
use App\Models\Course\Course;
use App\Models\User;
use App\Models\Users\Certificate;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ResultsController extends Controller
{
    /**
     * Mismo guard que Managers\Users\UsersController::guardNotSuperadmin():
     * un manager no-superadmin no puede ver los resultados de una cuenta
     * superadmin.
     */
    private function guardNotSuperadmin(User $user): void
    {
        abort_if(
            $user->role === 'superadmin' && auth()->user()->role !== 'superadmin',
            403,
            'No tienes autorización para gestionar esta cuenta.'
        );
    }

    public function index(Request $request, $slack)
    {

        $searchKey = $request->search;
        $course = $request->course;

        $courses = Course::latest()->get();
        $user = User::slack($slack);
        $this->guardNotSuperadmin($user);
        $certificates = $user->certificates();

        if ($searchKey) {
            $certificates = $certificates->where('firstname', 'like', '%'.$searchKey.'%');
        }

        if ($request->course != null) {
            $certificates = $certificates->where('course_id', $course);
        }

        $certificates = $certificates->paginate(paginationNumber());

        return view('managers.views.exams.results.index')->with([
            'certificates' => $certificates,
            'searchKey' => $searchKey,
            'courses' => $courses,
            'course' => $course,
        ]);
    }

    public function view($slack)
    {

        $certificate = Certificate::slack($slack);
        $this->authorize('view', $certificate);
        if ($certificate->user) {
            $this->guardNotSuperadmin($certificate->user);
        }

        $exam = $certificate->exam;
        $answers = $certificate->exam?->answers;
        $wrongs = $certificate->exam?->answers()?->wrong()->count();
        $corrects = $certificate->exam?->answers()?->correct()->count();

        return view('managers.views.exams.results.view')->with([
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
        $this->authorize('view', $certificate);

        $exam = $certificate->exam;
        $user = $certificate->user;
        $answers = $certificate->exam?->answers();

        // El cliente puede haberse borrado (soft delete) después de emitirse
        // el certificado; sin esto revienta al armar el nombre del archivo.
        abort_unless($user instanceof User, 404, 'El usuario de este certificado ya no existe.');
        $this->guardNotSuperadmin($user);

        return Excel::download(new ResultsExport($exam, $answers), $user->identification.'.xlsx');

    }
}
