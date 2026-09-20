<?php

namespace App\Http\Controllers\Supports\Users;

use App\Enums\OrderCondition as Condition;
use App\Http\Controllers\Concerns\RestrictsManageableUsers;
use App\Http\Controllers\Controller;
use App\Http\Requests\Supports\Users\UpdateUserOrderRequest;
use App\Models\Order\Order;
use App\Models\Order\OrderCondition;
use App\Models\Order\OrderMethod;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    use RestrictsManageableUsers;

    public function index(Request $request, $slack)
    {

        $user = $this->guardManageableUser(User::slack($slack));

        $orders = $user->orders()->latest()->paginate(paginationNumber());

        return view('supports.views.users.users.orders')->with([
            'user' => $user,
            'orders' => $orders,
        ]);

    }

    public function destroy($slack)
    {
        $order = Order::slack($slack);

        // El scope slack() devuelve el Builder cuando no hay match.
        if (! $order instanceof Order) {
            return redirect()->route('support.users');
        }

        $this->guardManageableOrderOwner($order);

        $userSlack = $order->user?->slack;

        // Las FK están en cascada (orders → inscriptions → certificates): borrar
        // una orden pagada eliminaría la matrícula del alumno Y su certificado
        // emitido, sin forma de deshacerlo. Primero hay que cambiarla de estado.
        if ($order->condition_id === Condition::Pagada->value) {
            // support.users.orders.index exige {slack}; pasar '' cuando el
            // cliente ya no existe (soft delete) lanzaba UrlGenerationException.
            $destino = $userSlack
                ? redirect()->route('support.users.orders.index', $userSlack)
                : redirect()->route('support.users');

            return $destino->with('error', 'No se puede eliminar una orden pagada: arrastraría en cascada las matrículas y certificados del alumno. Cambia primero su condición.');
        }

        $order->delete();

        if (! $userSlack) {
            return redirect()->route('support.users');
        }

        return redirect()->route('support.users.orders.index', $userSlack);

    }

    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'action' => ['required', 'in:delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:orders,id'],
        ]);

        // Mismo guard que destroy(): una orden pagada arrastraría en cascada
        // las matrículas y certificados del alumno, así que no se elimina en lote.
        // Tampoco se elimina en lote la orden de un usuario no gestionable
        // (manager/support) aunque su id venga en el payload.
        $orders = Order::whereIn('id', $request->ids)->get()
            ->filter(fn (Order $order) => ! $order->user || in_array($order->user->role, $this->manageableRoles, true));
        $deletable = $orders->reject(fn (Order $order) => $order->condition_id === Condition::Pagada->value);
        $skipped = $orders->count() - $deletable->count();

        match ($request->action) {
            'delete' => Order::whereIn('id', $deletable->pluck('id'))->delete(),
        };

        $message = $deletable->count().' orden(es) eliminadas.';

        if ($skipped > 0) {
            $message .= ' '.$skipped.' orden(es) pagada(s) no se eliminaron: cambia primero su condición.';
        }

        return response()->json(['success' => true, 'message' => $message]);
    }

    public function view($slack)
    {

        $order = Order::slack($slack);

        // El cliente puede haberse borrado (soft delete) después de la orden;
        // la vista lee $order->user->firstname sin null-check.
        abort_unless($order->user instanceof User, 404, 'El cliente de esta orden ya no existe.');

        $this->guardManageableOrderOwner($order);

        return view('supports.views.distributors.orders.orders.view')->with([
            'order' => $order,
        ]);

    }

    public function edit($slack)
    {
        $order = Order::slack($slack);

        $this->guardManageableOrderOwner($order);

        $conditions = OrderCondition::latest()->get()->pluck('title', 'id');
        $methods = OrderMethod::latest()->get()->pluck('title', 'id');

        return view('supports.views.users.users.orders_edit')->with([
            'order' => $order,
            'conditions' => $conditions,
            'methods' => $methods,
        ]);
    }

    public function update(UpdateUserOrderRequest $request)
    {
        $order = Order::slack($request->slack);

        if (! $order instanceof Order) {
            return response()->json(['success' => false, 'message' => 'La orden no existe.'], 404);
        }

        $this->guardManageableOrderOwner($order);

        // La condicion 4 ("Pagada") es la unica que conserva payment_at; cualquier
        // otra condicion debe limpiarla para no dejar una orden no-pagada con
        // una fecha de pago residual de un estado anterior.
        $order->payment_at = (int) $request->condition === 4
            ? Carbon::parse($request->payment)
            : null;

        $order->condition_id = $request->condition;
        $order->method_id = $request->methods;
        $order->update();

        return response()->json([
            'success' => true,
            'message' => 'Se ha actualizado correctamente',
            'data' => [
                'slack' => $order->slack,
            ],
        ]);
    }

    public function print($slack)
    {

        $order = Order::slack($slack);

        $this->guardManageableOrderOwner($order);

        return view('supports.views.distributors.orders.orders.print')->with([
            'order' => $order,
        ]);

    }

    /**
     * Aborta si la orden pertenece a un usuario con rol no gestionable por
     * soporte (manager/support): evita ver, editar o eliminar la orden de
     * una cuenta privilegiada solo con conocer su slack.
     */
    private function guardManageableOrderOwner($order): void
    {
        if (! $order instanceof Order) {
            return;
        }

        $owner = $order->user;

        abort_if(
            $owner && ! in_array($owner->role, $this->manageableRoles, true),
            403,
            'No tienes autorización para gestionar este recurso.'
        );
    }
}
