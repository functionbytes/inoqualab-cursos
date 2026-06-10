<?php

namespace App\Http\Controllers\Enterprises\Users;

use App\Http\Controllers\Controller;
use App\Models\Course\Course;
use App\Models\Inscription;
use App\Models\User;
use App\Models\Users\Certificate;
use Barryvdh\DomPDF\Facade\Pdf;
use DB;
use Illuminate\Http\Request;

class CertificatesController extends Controller
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
        $certificate = $inscription->certificate;
        $pdf = Pdf::loadView('enterprises.views.users.certificates.download', compact('certificate'))->setPaper('a4', 'landscape');

        return $pdf->stream("certificado_{$certificate->id}.pdf");

    }

    public function course($slack)
    {

        $certificate = Certificate::slack($slack);
        $pdf = Pdf::loadView('enterprises.views.users.certificates.download', compact('certificate'))->setPaper('a4', 'landscape');

        return $pdf->stream("certificado_{$certificate->id}.pdf");

    }

    public function broad($slack)
    {

        $user = User::slack($slack);
        $certificates = $user->certificates;
        $pdf = Pdf::loadView('enterprises.views.users.certificates.broad', compact('certificates'))->setPaper('a4', 'landscape');

        return $pdf->stream("certificados_{$user->identification}.pdf");

    }
}
