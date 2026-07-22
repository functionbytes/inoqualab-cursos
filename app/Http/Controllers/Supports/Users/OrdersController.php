<?php

namespace App\Http\Controllers\Supports\Users;

use App\Enums\OrderCondition as Condition;
use App\Http\Controllers\Controller;
use App\Http\Requests\Supports\Users\UpdateUserOrderRequest;
use App\Models\Order\Order;
use App\Models\Order\OrderCondition;
use App\Models\Order\OrderMethod;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    public function index(Request $request, $slack)
    {

        $user = User::slack($slack);

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

        $userSlack = $order->user?->slack;

        // Las FK están en cascada (orders → inscriptions → certificates): borrar
        // una orden pagada eliminaría la matrícula del alumno Y su certificado
        // emitido, sin forma de deshacerlo. Primero hay que cambiarla de estado.
        if ($order->condition_id === Condition::Pagada->value) {
            return redirect()
                ->route('support.users.orders.index', $userSlack ?? '')
                ->with('error', 'No se puede eliminar una orden pagada: arrastraría en cascada las matrículas y certificados del alumno. Cambia primero su condición.');
        }

        $order->delete();

        if (! $userSlack) {
            return redirect()->route('support.users');
        }

        return redirect()->route('support.users.orders.index', $userSlack);

    }

    public function view($slack)
    {

        $order = Order::slack($slack);

        return view('supports.views.distributors.orders.orders.view')->with([
            'order' => $order,
        ]);

    }

    public function edit($slack)
    {
        $order = Order::slack($slack);

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

        return view('supports.views.distributors.orders.orders.print')->with([
            'order' => $order,
        ]);

    }
}
