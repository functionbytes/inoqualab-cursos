<?php

namespace App\Http\Controllers\Managers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\BulkActionCartAbandonmentRequest;
use App\Mail\Customers\Orders\IncompleteCartMail;
use App\Models\CartAbandonment;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
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
            ->paginate(paginationNumber(25))
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

        $view = request()->ajax() ? 'managers.views.cart-abandonments._table' : 'managers.views.cart-abandonments.index';

        return view($view, compact(
            'abandonments', 'stats', 'search', 'status'
        ));
    }

    public function bulkAction(BulkActionCartAbandonmentRequest $request): JsonResponse
    {
        if ($request->action === 'remind') {
            return $this->bulkRemind($request->ids);
        }

        $query = CartAbandonment::whereIn('id', $request->ids);
        $count = $query->count();
        $query->delete();

        return response()->json(['success' => true, 'message' => $count.' registro(s) eliminados.']);
    }

    /**
     * Mismo guard que remind() individual (ya convertido / sin items), pero
     * en lote: los que no califican se cuentan como omitidos en vez de
     * abortar todo el bulk por un registro inválido.
     */
    private function bulkRemind(array $ids): JsonResponse
    {
        $abandonments = CartAbandonment::whereIn('id', $ids)->get();

        $sent = 0;
        $skipped = 0;

        foreach ($abandonments as $abandonment) {
            if ($abandonment->converted_at || empty($abandonment->items)) {
                $skipped++;

                continue;
            }

            Mail::to($abandonment->email)->queue(new IncompleteCartMail($abandonment));
            $abandonment->reminded_at = Carbon::now()->setTimezone('America/Bogota');
            $abandonment->save();
            $sent++;
        }

        $message = $sent.' recordatorio(s) enviado(s).';
        if ($skipped > 0) {
            $message .= ' '.$skipped.' omitido(s) (ya convertidos o sin ítems).';
        }

        return response()->json(['success' => true, 'message' => $message]);
    }

    /**
     * Envía manualmente el mismo correo de "carrito incompleto" que dispara el
     * comando programado (orders:remind-incomplete-carts), sin esperar la
     * ventana de 1h -- para cuando el manager quiere contactar ya al cliente
     * desde el detalle del carrito.
     */
    public function remind(CartAbandonment $cartAbandonment): JsonResponse
    {
        if ($cartAbandonment->converted_at) {
            return response()->json([
                'success' => false,
                'message' => 'Este carrito ya se convirtió en una orden.',
            ], 422);
        }

        if (empty($cartAbandonment->items)) {
            return response()->json([
                'success' => false,
                'message' => 'Este registro no tiene items guardados.',
            ], 422);
        }

        Mail::to($cartAbandonment->email)->queue(new IncompleteCartMail($cartAbandonment));

        $cartAbandonment->reminded_at = Carbon::now()->setTimezone('America/Bogota');
        $cartAbandonment->save();

        return response()->json([
            'success' => true,
            'message' => 'Recordatorio enviado a '.$cartAbandonment->email.'.',
        ]);
    }
}
