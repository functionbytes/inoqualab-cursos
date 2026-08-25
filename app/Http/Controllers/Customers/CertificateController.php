<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use App\Models\Users\Certificate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\View\View;

class CertificateController extends Controller
{
    public function index(): View
    {
        $user = app('customer');

        $certificates = Certificate::with('course')
            ->where('user_id', $user->id)
            ->latest('id')
            ->paginate(paginationNumber());

        $variant = portalVariant('customers_certificates_variant');

        return view('customers.views.certificates.index'.$variant, compact('certificates', 'user'));
    }

    public function view(string $slack): View
    {
        $user = app('customer');

        $certificate = Certificate::with(['user', 'course', 'certifier'])
            ->where('slack', $slack)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return view('customers.views.certificates.view', compact('certificate'));
    }

    public function download(string $slack)
    {
        return $this->render($slack)->download("certificado_{$slack}.pdf");
    }

    private function render(string $slack)
    {
        $user = app('customer');
        $certificate = Certificate::where('slack', $slack)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return Pdf::loadView('customers.views.certificates.download', compact('certificate'))
            ->setPaper('a4', 'landscape');
    }
}
