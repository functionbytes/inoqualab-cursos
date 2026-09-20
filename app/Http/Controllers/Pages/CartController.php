<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Bundle\Bundle;
use App\Models\CartAbandonment;
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
        // Un curso se matricula una sola vez por usuario: el checkout fuerza qty=1
        // (cartCheckoutLines), así que el carrito debe mostrar lo mismo o el total
        // del drawer no coincidiría con lo que realmente se cobra.
        $qty = $type === 'course' ? 1 : max(1, min(10, (int) $request->input('qty', 1)));

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

        // Misma regla que en add()/cartCheckoutLines: los cursos siempre qty=1.
        $cart[$request->key]['qty'] = ($cart[$request->key]['type'] ?? null) === 'course'
            ? 1
            : (int) $request->qty;
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

    /**
     * Captura temprana de "carrito incompleto": se llama desde el checkout apenas
     * se conoce el correo (blur del campo para invitados, o carga de página para
     * autenticados) -- MUCHO antes de que exista una Order real. Sin esto, alguien
     * que llega por pauta, escribe su correo y se va sin terminar el formulario
     * nunca queda registrado en ningún lado (orders:remind-abandoned solo ve
     * órdenes ya generadas). Se guarda como intento "pendiente"; si más tarde sí
     * genera la orden, generate() marca converted_at y este flujo deja de recordarle.
     */
    public function captureLead(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:191'],
        ]);

        [$lines, $subtotal] = cartCheckoutLines();

        if (empty($lines)) {
            return response()->json(['success' => true, 'tracked' => false]);
        }

        $email = mb_strtolower(trim($request->email));
        $user = auth()->user();

        $abandonment = CartAbandonment::where('email', $email)->whereNull('converted_at')->first();

        if (! $abandonment) {
            $abandonment = new CartAbandonment;
            $abandonment->slack = $this->generate_slack('cart_abandonments');
            $abandonment->email = $email;
        }

        $abandonment->user_id = $user?->id;
        $abandonment->items = $lines;
        $abandonment->total = $subtotal;
        // Cada nueva captura es una señal fresca de interés (el carrito pudo cambiar):
        // se resetea reminded_at para que vuelva a entrar en la ventana de recordatorio.
        $abandonment->reminded_at = null;
        $abandonment->save();

        return response()->json(['success' => true, 'tracked' => true]);
    }

    /**
     * Restaura un carrito capturado (link del correo de "carrito incompleto") y
     * lleva directo al checkout -- el enlace no sirve de nada si el cliente tiene
     * que volver a armar el carrito desde cero.
     */
    public function restore(string $slack)
    {
        $abandonment = CartAbandonment::where('slack', $slack)->whereNull('converted_at')->first();

        if (! $abandonment) {
            return redirect()->route('courses')->with('info', 'Este enlace ya no es válido.');
        }

        // Se re-resuelve cada línea contra el curso/paquete actual (no se confía en
        // el snapshot guardado): la disponibilidad y el precio pudieron cambiar
        // desde que se capturó el intento, igual que ya valida add().
        $cart = [];
        foreach ($abandonment->items as $line) {
            $item = $line['type'] === 'bundle'
                ? Bundle::where('slack', $line['slack'])->where('available', 1)->first()
                : Course::where('slack', $line['slack'])->where('available', 1)->first();

            if (! $item) {
                continue;
            }

            if ($line['type'] === 'course') {
                $onSale = $item->payment != 0 && $item->promotion == 1 && $item->discount < $item->price;
                $price = $item->payment == 0 ? 0 : ($onSale ? $item->discount : $item->price);
                $compare = $onSale ? $item->price : null;
                $thumb = optional($item->getFirstMedia('thumbnail'))->getFullUrl();
            } else {
                $price = $item->price;
                $compare = null;
                $thumb = optional($item->courses()->first()?->getFirstMedia('thumbnail'))->getFullUrl();
            }

            $cart[$line['type'].'_'.$line['slack']] = [
                'type' => $line['type'],
                'slack' => $line['slack'],
                'title' => $item->title,
                'price' => $price ?? 0,
                'compare' => $compare,
                'qty' => $line['type'] === 'course' ? 1 : ($line['qty'] ?? 1),
                'image' => $thumb,
            ];
        }
        session(['cart' => $cart]);

        return redirect()->route('checkout.cart');
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
