<?php

namespace App\Http\Controllers\Distributors\Users;

use App\Http\Controllers\Controller;
use App\Models\Course\Course;
use App\Models\Enterprise\EnterpriseUser;
use App\Models\Inscription;
use App\Models\User;
use App\Models\Users\Certificate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class CertificatesController extends Controller
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

        $courses = Course::latest()->get();
        $user = $this->managedUser($slack);
        $certificates = $user->certificates()->latest()->with('course');

        if ($searchKey) {
            $certificates = $certificates->where('firstname', 'like', '%'.$searchKey.'%');
        }

        if ($request->course != null) {
            $certificates = $certificates->where('course_id', $course);
        }

        $certificates = $certificates->paginate(paginationNumber());

        return view('distributors.views.enterprises.users.certificates.index')->with([
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
        $this->assertManagedUser($inscription->user_id);
        $certificate = $inscription->certificate;
        // El curso puede no estar completado todavía: sin certificado emitido,
        // la vista revienta al leer sus propiedades.
        abort_unless($certificate instanceof Certificate, 404, 'El certificado de esta inscripción aún no está disponible.');
        $pdf = Pdf::loadview('distributors.views.enterprises.users.certificates.download', compact('certificate'))->setPaper('A4', 'landscape');

        return $pdf->stream();

    }

    public function course($slack)
    {

        $certificate = Certificate::slack($slack);
        $this->assertManagedUser($certificate->user_id);
        $pdf = Pdf::loadview('distributors.views.enterprises.users.certificates.download', compact('certificate'))->setPaper('a4', 'landscape');

        return $pdf->stream();

    }

    public function broad($slack)
    {

        $user = $this->managedUser($slack);
        $certificates = $user->certificates;
        $pdf = Pdf::loadview('distributors.views.enterprises.users.certificates.broad', compact('certificates'))->setWarnings(false)->setPaper('a4', 'landscape');

        return $pdf->stream();

    }
}
