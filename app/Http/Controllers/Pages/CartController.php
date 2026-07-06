<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Bundle\Bundle;
use App\Models\Course\Course;
use App\Models\Inscription;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart = session('cart', []);

        return view('pages.views.cart.index', compact('cart'));
    }

    /**
     * Contenido del carrito deslizable (drawer) — partial renderizado desde la sesión.
     */
    public function drawer()
    {
        return view('pages.includes.cart-drawer-content');
    }

    public function add(Request $request)
    {
        $request->validate([
            'type' => 'required|in:course,bundle',
            'slack' => 'required|string',
            'qty' => 'nullable|integer|min:1|max:10',
        ]);

        $type = $request->type;
        $slack = $request->slack;
        $qty = max(1, min(10, (int) $request->input('qty', 1)));

        $item = $type === 'bundle'
            ? Bundle::where('slack', $slack)->first()
            : Course::where('slack', $slack)->first();

        if (! $item) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Producto no encontrado.'], 404);
            }

            return back()->with('error', 'Producto no encontrado.');
        }

        // Rechazar ítems retirados de la venta (no disponibles).
        if ($item->available != 1) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Este producto ya no está disponible.'], 422);
            }

            return back()->with('error', 'Este producto ya no está disponible.');
        }

        // Para usuarios autenticados, evitar recomprar un curso con inscripción ACTIVA
        // (las inscripciones expiradas sí pueden re-comprarse como renovación).
        if ($type === 'course' && auth()->check()) {
            $activeEnrollment = Inscription::where('user_id', auth()->id())
                ->where('course_id', $item->id)
                ->where('expire', 0)
                ->exists();

            if ($activeEnrollment) {
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Ya estás inscrito en este curso.'], 422);
                }

                return back()->with('error', 'Ya estás inscrito en este curso.');
            }
        }

        // Precio efectivo (promotion-aware y gratis-aware), consistente con el checkout.
        // $compare = precio original tachado cuando hay promoción.
        $compare = null;
        if ($type === 'course') {
            $onSale = $item->payment != 0 && $item->promotion == 1 && $item->discount < $item->price;
            $price = $item->payment == 0 ? 0 : ($onSale ? $item->discount : $item->price);
            $compare = $onSale ? $item->price : null;
        } else {
            $price = $item->price;
        }
        $price = $price ?? 0;

        $cart = session('cart', []);
        $key = $type.'_'.$slack;
        $alreadyInCart = isset($cart[$key]);

        if (! $alreadyInCart) {
            $thumb = $type === 'bundle'
                ? optional($item->courses()->first()?->getFirstMedia('thumbnail'))->getFullUrl()
                : optional($item->getFirstMedia('thumbnail'))->getFullUrl();

            $cart[$key] = [
                'type' => $type,
                'slack' => $slack,
                'title' => $item->title,
                'price' => $price,
                'compare' => $compare,
                'qty' => $qty,
                'image' => $thumb,
            ];
            session(['cart' => $cart]);
        } elseif ($request->filled('qty')) {
            // Si ya existe y se especifica cantidad, la actualizamos.
            $cart[$key]['qty'] = $qty;
            session(['cart' => $cart]);
        }

        if ($request->boolean('buy_now')) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'buy_now' => true,
                    'redirect' => route('checkout.cart'),
                ]);
            }

            return redirect()->route('checkout.cart');
        }

        if ($request->ajax()) {
            $qty = session("cart.{$key}.qty", 1);

            return response()->json([
                'success' => true,
                'already_in_cart' => $alreadyInCart,
                'item' => [
                    'title' => $item->title,
                    'price' => $price,
                    'type' => $type,
                    'slack' => $slack,
                    'qty' => $qty,
                    'line_total' => $price * $qty,
                    'image' => session("cart.{$key}.image"),
                ],
                'cart_count' => cartUnits(),
                'cart_total' => $this->cartTotal(),
            ]);
        }

        return back()->with('success', 'Agregado al carrito.');
    }

    public function remove(Request $request)
    {
        $request->validate(['key' => 'required|string']);

        $cart = session('cart', []);
        unset($cart[$request->key]);
        session(['cart' => $cart]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'cart_count' => cartUnits(),
                'cart_total' => $this->cartTotal(),
                'message' => 'Eliminado del carrito.',
            ]);
        }

        return back()->with('success', 'Eliminado del carrito.');
    }

    public function updateQty(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'qty' => 'required|integer|min:1|max:10',
        ]);

        $cart = session('cart', []);

        if (! isset($cart[$request->key])) {
            return response()->json(['success' => false, 'message' => 'Producto no encontrado.'], 404);
        }

        $cart[$request->key]['qty'] = (int) $request->qty;
        session(['cart' => $cart]);

        $item = $cart[$request->key];
        $lineTotal = ($item['price'] ?? 0) * $item['qty'];

        return response()->json([
            'success' => true,
            'qty' => $item['qty'],
            'line_total' => $lineTotal,
            'cart_total' => $this->cartTotal(),
        ]);
    }

    /**
     * Total del carrito considerando la cantidad de cada línea.
     */
    private function cartTotal(): float
    {
        return array_sum(array_map(
            fn ($item) => ($item['price'] ?? 0) * ($item['qty'] ?? 1),
            session('cart', [])
        ));
    }

    public function clear(Request $request)
    {
        session()->forget('cart');

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'cart_count' => 0,
                'cart_total' => 0,
                'message' => 'Carrito vaciado.',
            ]);
        }

        return back()->with('success', 'Carrito vaciado.');
    }
}
