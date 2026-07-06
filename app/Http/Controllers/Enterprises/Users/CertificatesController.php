<?php

namespace App\Http\Controllers\Enterprises\Users;

use App\Http\Controllers\Controller;
use App\Models\Course\Course;
use App\Models\Inscription;
use App\Models\User;
use App\Models\Users\Certificate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class CertificatesController extends Controller
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

        return view('enterprises.views.users.certificates.index', [
            'certificates' => $certificates,
            'searchKey' => $searchKey,
            'courses' => $courses,
            'course' => $course,
            'user' => $user,
            'years' => $years,
            'year' => $year,
        ]);

    }

    public function user($slack)
    {

        $inscription = Inscription::slack($slack);
        $this->assertEnterpriseUser($inscription->user_id);
        $certificate = $inscription->certificate;
        $pdf = Pdf::loadView('enterprises.views.users.certificates.download', compact('certificate'))->setPaper('a4', 'landscape');

        return $pdf->stream("certificado_{$certificate->id}.pdf");

    }

    public function course($slack)
    {

        $certificate = Certificate::slack($slack);
        $this->assertEnterpriseUser($certificate->user_id);
        $pdf = Pdf::loadView('enterprises.views.users.certificates.download', compact('certificate'))->setPaper('a4', 'landscape');

        return $pdf->stream("certificado_{$certificate->id}.pdf");

    }

    public function broad($slack)
    {

        $user = $this->managedUser($slack);
        $certificates = $user->certificates()
            ->with(['user', 'course', 'certification.media', 'certifier.media'])
            ->get();
        $pdf = Pdf::loadView('enterprises.views.users.certificates.broad', compact('certificates'))->setPaper('a4', 'landscape');

        return $pdf->stream("certificados_{$user->identification}.pdf");

    }
}
