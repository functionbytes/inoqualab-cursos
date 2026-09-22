<?php

namespace App\Http\Controllers\Managers\Users;

use App\Http\Controllers\Controller;
use App\Models\Course\Course;
use App\Models\Inscription;
use App\Models\User;
use App\Models\Users\Certificate;
use Illuminate\Http\Request;

class CertificatesController extends Controller
{
    /**
     * Mismo guard que Managers\Users\UsersController::guardNotSuperadmin():
     * un manager no-superadmin no puede ver los certificados de una cuenta
     * superadmin. UserPolicy::view() no distingue el rol del modelo, asi
     * que $this->authorize('view', $user) por si solo no basta.
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
        $certificates = $user->certificates()->latest()->with('course');

        if ($searchKey) {
            $certificates = $certificates->where('firstname', 'like', '%'.$searchKey.'%');
        }

        if ($request->course != null) {
            $certificates = $certificates->where('course_id', $course);
        }

        $certificates = $certificates->paginate(paginationNumber());

        $view = request()->ajax() ? 'managers.views.users.certificates._table' : 'managers.views.users.certificates.index';

        return view($view)->with([
            'certificates' => $certificates,
            'searchKey' => $searchKey,
            'courses' => $courses,
            'course' => $course,
            'user' => $user,
        ]);

    }

    public function download($slack)
    {

        $certificate = Certificate::slack($slack);
        $this->authorize('view', $certificate);
        if ($certificate->user) {
            $this->guardNotSuperadmin($certificate->user);
        }

        return $this->streamCertificate($certificate);

    }

    public function user($slack)
    {

        // Las vistas enlazan esta ruta con el slack de la INSCRIPCIÓN (mismo
        // contrato que Supports\Users\CertificatesController::user). La versión
        // anterior lo trataba como Order del esquema legacy mono-item y
        // fabricaba un Certificate con columnas inexistentes (500 garantizado,
        // y además emitía certificados sin examen aprobado).
        $inscription = Inscription::slack($slack);

        abort_unless($inscription instanceof Inscription, 404);

        $certificate = $inscription->certificate;

        abort_unless($certificate instanceof Certificate, 404, 'El alumno aún no tiene certificado emitido para este curso.');

        $this->authorize('view', $certificate);
        if ($certificate->user) {
            $this->guardNotSuperadmin($certificate->user);
        }

        return $this->streamCertificate($certificate);

    }

    public function course($slack)
    {

        $certificate = Certificate::slack($slack);
        $this->authorize('view', $certificate);
        if ($certificate->user) {
            $this->guardNotSuperadmin($certificate->user);
        }

        return $this->streamCertificate($certificate);

    }

    public function broad($slack)
    {

        $user = User::slack($slack);
        $this->authorize('view', $user);
        $this->guardNotSuperadmin($user);

        $certificates = $user->certificates;
        $pdf = \Pdf::loadView('managers.views.users.certificates.broad', compact('certificates'))->setPaper('a4', 'landscape');

        return $pdf->stream();

    }

    private function streamCertificate(Certificate $certificate)
    {
        return \Pdf::loadView('managers.views.users.certificates.download', compact('certificate'))
            ->setPaper('a4', 'landscape')
            ->stream();
    }
}
