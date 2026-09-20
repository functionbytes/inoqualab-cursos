<?php

namespace App\Http\Controllers\Managers;

use App\Http\Controllers\Controller;
use App\Models\CartAbandonment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartAbandonmentsController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $status = $request->query('status');

        $abandonments = CartAbandonment::query()
            ->with('user:id,firstname,lastname')
            ->when($search, fn ($q) => $q->where('email', 'like', "%{$search}%"))
            ->when($status === 'pending', fn ($q) => $q->whereNull('reminded_at')->whereNull('converted_at'))
            ->when($status === 'reminded', fn ($q) => $q->whereNotNull('reminded_at')->whereNull('converted_at'))
            ->when($status === 'converted', fn ($q) => $q->whereNotNull('converted_at'))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        // Sin Cache::remember: a diferencia de newsletter (miles de filas,
        // consulta cara), esta tabla es chica y de bajo tráfico -- el costo de
        // una fila obsoleta (ej. justo después de convertir uno) no vale la
        // complejidad extra de invalidar el cache en cada punto de escritura.
        $row = CartAbandonment::query()
            ->selectRaw(
                'COUNT(*) as total,
                 SUM(converted_at IS NOT NULL) as converted,
                 SUM(converted_at IS NULL AND reminded_at IS NOT NULL) as reminded,
                 SUM(converted_at IS NULL AND reminded_at IS NULL) as pending'
            )
            ->first();

        $stats = [
            'total' => (int) $row->total,
            'converted' => (int) $row->converted,
            'reminded' => (int) $row->reminded,
            'pending' => (int) $row->pending,
            'conversion_rate' => $row->total > 0 ? round($row->converted / $row->total * 100, 1) : 0.0,
        ];

        return view('managers.views.cart-abandonments.index', compact(
            'abandonments', 'stats', 'search', 'status'
        ));
    }

    // Sin abort_unless(): el resto del controller (index()) tampoco valida
    // permisos Spatie explícitos -- se apoya en el middleware de rol/panel de
    // la ruta, igual que aquí.
    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'action' => ['required', 'in:delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:cart_abandonments,id'],
        ]);

        $query = CartAbandonment::whereIn('id', $request->ids);
        $count = $query->count();
        $query->delete();

        return response()->json(['success' => true, 'message' => $count.' registro(s) eliminados.']);
    }
}
