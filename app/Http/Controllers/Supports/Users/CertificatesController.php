<?php

namespace App\Http\Controllers\Supports\Users;

use App\Http\Controllers\Controller;
use App\Models\Course\Course;
use App\Models\Inscription;
use App\Models\User;
use App\Models\Users\Certificate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class CertificatesController extends Controller
{
    public function index(Request $request, $slack)
    {

        $searchKey = $request->search;
        $course = $request->course;

        $courses = Course::latest()->get();
        $user = User::slack($slack);
        $certificates = $user->certificates()->latest();

        if ($searchKey) {
            $certificates = $certificates->where('firstname', 'like', '%'.$searchKey.'%');
        }

        if ($request->course != null) {
            $certificates = $certificates->where('course_id', $course);
        }

        $certificates = $certificates->paginate(paginationNumber());

        return view('supports.views.enterprises.users.certificates.index')->with([
            'certificates' => $certificates,
            'searchKey' => $searchKey,
            'courses' => $courses,
            'course' => $course,
            'user' => $user,
        ]);

    }

    public function user($slack)
    {

        $inscription = Inscription::slack($slack);
        $certificate = $inscription->certificate;
        // El curso puede no estar completado todavía: sin certificado emitido,
        // la vista revienta al leer sus propiedades.
        abort_unless($certificate instanceof Certificate, 404, 'El certificado de esta inscripción aún no está disponible.');
        $pdf = Pdf::loadview('supports.views.enterprises.users.certificates.download', compact('certificate'))->setPaper('A4', 'landscape');

        return $pdf->stream();

    }

    public function course($slack)
    {

        $certificate = Certificate::slack($slack);
        $pdf = Pdf::loadview('supports.views.enterprises.users.certificates.download', compact('certificate'))->setPaper('a4', 'landscape');

        return $pdf->stream();

    }

    public function broad($slack)
    {

        $user = User::slack($slack);
        $certificates = $user->certificates;
        $pdf = Pdf::loadview('supports.views.enterprises.users.certificates.broad', compact('certificates'))->setWarnings(false)->setPaper('a4', 'landscape');

        return $pdf->stream();

    }
}
