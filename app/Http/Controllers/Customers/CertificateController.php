<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use App\Models\Users\Certificate;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CertificateController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $user = app('customer');
        $state = $request->state;
        $searchKey = $request->search;

        // Mismo corte que ya usaba la vista para pintar el chip de cada
        // tarjeta (Vigente/Por vencer/Vencido): "hoy" y "hoy + 30 días", los
        // dos límites que separan los 4 estados. Se calculan una sola vez acá
        // para que el filtro (WHERE) y los contadores usen exactamente el
        // mismo corte.
        $today = Carbon::today();
        $soonLimit = Carbon::today()->addDays(30);

        $baseQuery = fn () => Certificate::where('user_id', $user->id);

        // Contador por estado sobre TODOS los certificados del usuario, no
        // solo la página cargada -- antes se calculaba en la vista a partir
        // de los 15 items ya paginados, así que un certificado vencido en la
        // página 2 no se sumaba al contador "Vencidos" visible en la página 1.
        $counts = [
            'todos' => $baseQuery()->count(),
            'valid' => $baseQuery()->whereNotNull('end_at')->whereDate('end_at', '>', $soonLimit)->count(),
            'soon' => $baseQuery()->whereNotNull('end_at')->whereDate('end_at', '>=', $today)->whereDate('end_at', '<=', $soonLimit)->count(),
            'expired' => $baseQuery()->whereNotNull('end_at')->whereDate('end_at', '<', $today)->count(),
        ];

        // latest('start_at'), no 'id': una renovación actualiza start_at/end_at
        // sobre el mismo registro (mismo id), así que ordenar por id dejaría
        // certificados renovados hace poco fuera de lugar al agrupar por año.
        $certificates = Certificate::with('course')->where('user_id', $user->id);

        if ($state === 'valid') {
            $certificates->whereNotNull('end_at')->whereDate('end_at', '>', $soonLimit);
        } elseif ($state === 'soon') {
            $certificates->whereNotNull('end_at')->whereDate('end_at', '>=', $today)->whereDate('end_at', '<=', $soonLimit);
        } elseif ($state === 'expired') {
            $certificates->whereNotNull('end_at')->whereDate('end_at', '<', $today);
        }

        // Por número de certificado o por el título del curso -- son los dos
        // datos visibles en cada tarjeta que el usuario podría recordar y
        // teclear (igual que Mis pedidos busca por número de orden).
        if ($searchKey) {
            $certificates->where(function ($query) use ($searchKey) {
                $query->where('slack', 'like', '%'.$searchKey.'%')
                    ->orWhereHas('course', function ($courseQuery) use ($searchKey) {
                        $courseQuery->where('title', 'like', '%'.$searchKey.'%');
                    });
            });
        }

        $certificates = $certificates->latest('start_at')->paginate(paginationNumber());

        $variant = portalVariant('customers_certificates_variant');

        // El filtro por estado y el paginador se resuelven por AJAX (ver el
        // script en certificates/index.blade.php): se devuelve solo el
        // fragmento re-renderizado en vez de la página completa.
        if ($request->ajax()) {
            return response()->json([
                'html' => view('customers.partials.views.certificates.list', compact('certificates', 'user', 'counts', 'state', 'searchKey'))->render(),
                'total' => $certificates->total(),
                'label' => Str::plural('certificado', $certificates->total()),
            ]);
        }

        return view('customers.views.certificates.index'.$variant, compact('certificates', 'user', 'counts', 'state', 'searchKey'));
    }

    public function view(string $slack): View
    {
        $user = app('customer');

        $certificate = Certificate::with(['user', 'course', 'certifier', 'certification'])
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
