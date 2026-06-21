<?php

namespace App\Http\Controllers\Pages;

use App\Events\Inscriptions\InscriptionCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRegisterRequest;
use App\Mail\Customers\Orders\ApprovedMails;
use App\Mail\Customers\Orders\PendingMails;
use App\Mail\Customers\Orders\VoidedMails;
use App\Model\Wompi;
use App\Models\Bundle\Bundle;
use App\Models\Citie;
use App\Models\Coupon\Coupon;
use App\Models\Coupon\CouponUsage;
use App\Models\Course\Course;
use App\Models\Inscription;
use App\Models\Order\Order;
use App\Models\Order\OrderCondition;
use App\Models\Order\OrderItem;
use App\Models\Order\OrderMethod;
use App\Models\Order\OrderType;
use App\Models\Order\PaymentEvent;
use App\Models\User;
use App\Models\Users\Certificate;
use App\Services\WompiService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckoutController extends Controller
{
    /**
     * Checkout del carrito completo (multi-item).
     */
    public function checkout(Request $request)
    {
        seo()->noindex(true);

        [$lines, $subtotal] = cartCheckoutLines();

        if (empty($lines)) {
            return redirect()->route('cart.index')->with('error', 'Tu carrito está vacío.');
        }

        removeCoupon();

        $items = collect($lines)->map(fn ($line) => (object) $line);

        $discount = 0;
        $total = $subtotal;
        $coupon = getCoupon();
        $date = new Carbon;
        $user = User::auth();

        if ($user != null) {
            $citie = $user->citie_id;
            $cities = Citie::where('id', $user->citie_id)->get()->pluck('title', 'id');
        } else {
            $citie = null;
            $cities = [];
        }

        return view('pages.views.payments.register')->with([
            'items' => $items,
            'coupon' => $coupon,
            'discount' => $discount,
            'subtotal' => $subtotal,
            'total' => $total,
            'cities' => $cities,
            'citie' => $citie,
            'date' => $date,
            'user' => $user,
        ]);

    }

    /**
     * Compra directa de un item: lo agrega al carrito y redirige al checkout del carrito.
     * Conserva la compatibilidad de los enlaces antiguos /checkout/{type}/{course}.
     */
    public function checkoutDirect($type, $slack)
    {

        $item = $type === 'bundle'
            ? Bundle::slack($slack)
            : Course::slack($slack);

        // El scope slack() devuelve el Builder (no null) cuando no hay match,
        // por eso se comprueba el tipo concreto antes de usar el item.
        if ($item instanceof Bundle || $item instanceof Course) {
            $cart = session('cart', []);
            $key = $type.'_'.$slack;

            if (! isset($cart[$key])) {
                $compare = null;
                if ($type === 'course') {
                    $onSale = $item->payment != 0 && $item->promotion == 1 && $item->discount < $item->price;
                    $price = $item->payment == 0 ? 0 : ($onSale ? $item->discount : $item->price);
                    $compare = $onSale ? $item->price : null;
                } else {
                    $price = $item->price;
                }

                $thumb = $type === 'bundle'
                    ? optional($item->courses()->first()?->getFirstMedia('thumbnail'))->getFullUrl()
                    : optional($item->getFirstMedia('thumbnail'))->getFullUrl();

                $cart[$key] = [
                    'type' => $type,
                    'slack' => $slack,
                    'title' => $item->title,
                    'price' => $price ?? 0,
                    'compare' => $compare,
                    'qty' => 1,
                    'image' => $thumb,
                ];
                session(['cart' => $cart]);
            }
        }

        return redirect()->route('checkout.cart');
    }

    public function register(CheckoutRegisterRequest $request)
    {

        $user = User::auth();

        // Si el usuario ya está autenticado, actualizar sus datos directamente
        if ($user) {
            $user->firstname = $request->firstname;
            $user->lastname = $request->lastname;
            $user->identification = $request->identification;
            $user->identification_type = $request->identification_type;
            $user->newsletter_notification = $request->newsletter ? 1 : 0;
            $user->cellphone = $request->cellphone;
            $user->company = $request->company;
            $user->address = $request->address;
            $user->citie_id = $request->citie;
            $user->terms = 1;
            $user->updated_at = Carbon::now()->setTimezone('America/Bogota');
            $user->save();

            return 'success';
        }

        $existingUser = User::where('email', $request->email)->first();

        if ($existingUser) {
            return 'email';
        } else {

            $user = new User;
            $user->slack = $this->generate_slack('users');
            $user->firstname = $request->firstname;
            $user->lastname = $request->lastname;
            $user->identification = $request->identification;
            $user->identification_type = $request->identification_type;
            $user->newsletter_notification = $request->newsletter ? 1 : 0;
            $user->cellphone = $request->cellphone;
            $user->company = $request->company;

            if ($request->password != null) {
                // El mutator password() del modelo User hashea automáticamente;
                // Hash::make aquí causaría doble-hash (login imposible).
                $user->password = $request->password;
            }
            $user->available = 1;
            $user->address = $request->address;
            $user->email = $request->email;
            $user->role = 'customer';
            $user->citie_id = $request->citie;
            $user->validation = 1;
            $user->terms = 1;
            $user->created_at = Carbon::now()->setTimezone('America/Bogota');
            $user->updated_at = Carbon::now()->setTimezone('America/Bogota');
            $user->save();

            $this->guard()->login($user);

            return 'success';

        }

    }

    /**
     * Página de checkout simulado (solo en modo sandbox). Muestra el resumen
     * de la orden y botones para simular el resultado del pago.
     */
    public function sandbox(string $slack)
    {
        if (setting('wompi_sandbox') !== 'true') {
            return redirect()->route('index');
        }

        $order = Order::slack($slack);

        if (! $order instanceof Order || ! auth()->check() || $order->user_id !== auth()->id()) {
            abort(404);
        }

        // Si ya está pagada, ir directo a la confirmación.
        if ($order->condition_id === 4) {
            return redirect()->route('payments.status', [$order->slack, 'APPROVED']);
        }

        return view('pages.views.payments.sandbox', ['order' => $order]);
    }

    public function simulate(string $reference, string $status)
    {
        if (setting('wompi_sandbox') !== 'true') {
            return redirect()->route('index');
        }

        // Solo el dueño de la orden puede simular su pago (evita que un tercero
        // marque APPROVED cualquier orden por referencia con sandbox activo).
        $order = Order::slack($reference);

        if (! $order instanceof Order || ! auth()->check() || $order->user_id !== auth()->id()) {
            abort(404);
        }

        $this->processOrderStatus($reference, 'SANDBOX-'.strtoupper($status).'-'.$reference, $status);

        return redirect()->route('payments.status', [$reference, $status]);
    }

    public function response(Request $request)
    {
        $transactionId = $request->input('id');

        if (! $transactionId) {
            return redirect()->route('index');
        }

        // No se confía en el status del query string: se consulta el estado real a Wompi
        $service = new WompiService;
        $transaction = $service->getTransaction($transactionId);

        if (! $transaction) {
            return redirect()->route('index');
        }

        $this->processOrderStatus(
            $transaction['reference'],
            $transaction['id'],
            $transaction['status'],
            isset($transaction['amount_in_cents']) ? (int) $transaction['amount_in_cents'] : null,
            $transaction['currency'] ?? null,
            $transaction['payment_method_type'] ?? null
        );

        return redirect()->route('payments.status', [$transaction['reference'], $transaction['status']]);
    }

    public function processing(Request $request)
    {
        $service = new WompiService;
        $transaction = $service->getTransaction($request->input('id', ''));

        if (! $transaction) {
            return redirect()->route('index');
        }

        $this->processOrderStatus(
            $transaction['reference'],
            $transaction['id'],
            $transaction['status'],
            isset($transaction['amount_in_cents']) ? (int) $transaction['amount_in_cents'] : null,
            $transaction['currency'] ?? null,
            $transaction['payment_method_type'] ?? null
        );

        return redirect()->route('payments.status', [$transaction['reference'], $transaction['status']]);
    }

    public function webhook(Request $request)
    {
        $payload = $request->all();
        $checksum = $request->header('X-Event-Checksum', '');

        $service = new WompiService;

        // Verificar SIEMPRE la firma (un webhook sin firma o inválido no se procesa)
        if (! $service->verifyWebhookSignature($payload, $checksum)) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $transaction = $payload['data']['transaction'] ?? null;

        if (! $transaction) {
            return response()->json(['message' => 'No transaction data'], 400);
        }

        // Idempotencia: cada (transacción, estado) se procesa una sola vez. Si Wompi
        // reintenta el webhook, se acusa recibo sin reprocesar.
        $event = PaymentEvent::firstOrCreate(
            ['transaction_id' => $transaction['id'], 'status' => $transaction['status']],
            ['reference' => $transaction['reference'] ?? null, 'payload' => $payload],
        );

        if (! $event->wasRecentlyCreated) {
            return response()->json(['message' => 'Already processed'], 200);
        }

        $this->processOrderStatus(
            $transaction['reference'],
            $transaction['id'],
            $transaction['status'],
            isset($transaction['amount_in_cents']) ? (int) $transaction['amount_in_cents'] : null,
            $transaction['currency'] ?? null,
            $transaction['payment_method_type'] ?? null
        );

        return response()->json(['message' => 'OK'], 200);
    }

    /**
     * Mapea el payment_method_type de Wompi a un OrderMethod local (lo crea si no existe).
     */
    private function paymentMethodIdFor(?string $type): ?int
    {
        $map = [
            'CARD' => ['card', 'Tarjeta'],
            'NEQUI' => ['nequi', 'Nequi'],
            'PSE' => ['pse', 'PSE'],
            'BANCOLOMBIA_TRANSFER' => ['bancolombia', 'Bancolombia'],
            'BANCOLOMBIA_QR' => ['bancolombia', 'Bancolombia'],
            'BANCOLOMBIA_COLLECT' => ['bancolombia', 'Bancolombia'],
            'DAVIPLATA' => ['daviplata', 'Daviplata'],
            'PCOL' => ['pcol', 'Botón Bancolombia'],
        ];

        $key = strtoupper(trim((string) $type));

        if ($key === '' || ! isset($map[$key])) {
            return null;
        }

        [$slug, $title] = $map[$key];

        return OrderMethod::firstOrCreate(['slug' => $slug], ['slack' => $slug, 'title' => $title])->id;
    }

    public function processOrderStatus(string $reference, string $transactionId, string $status, ?int $amountInCents = null, ?string $currency = null, ?string $paymentMethodType = null): void
    {
        $order = Order::slack($reference);

        if (! $order instanceof Order || $order->condition_id === 4) {
            return;
        }

        $order->transaction = $transactionId;
        $order->updated_at = Carbon::now()->setTimezone('America/Bogota');

        if ($status === 'APPROVED') {
            // Validar moneda: solo se acepta COP (evita pagos en otra divisa con el mismo número).
            if ($currency !== null && strtoupper($currency) !== 'COP') {
                Log::warning("Pago con moneda inesperada en orden {$order->slack}: recibido {$currency}, esperado COP.");

                return;
            }

            // Validar que el monto pagado coincide con el total de la orden.
            // Evita que se acepte un pago por un importe menor al debido.
            if ($amountInCents !== null) {
                $expected = (int) round($order->total_order_amount * 100);
                if ($amountInCents !== $expected) {
                    Log::warning("Pago con monto incorrecto en orden {$order->slack}: esperado {$expected} cents, recibido {$amountInCents} cents.");

                    return;
                }
            }

            // Transición atómica: solo un proceso puede pasar la orden a pagada.
            // Evita inscripciones duplicadas ante webhooks/redirects concurrentes.
            $claimed = Order::where('id', $order->id)
                ->where('condition_id', '!=', 4)
                ->update([
                    'condition_id' => 4,
                    'payment_at' => Carbon::now()->setTimezone('America/Bogota'),
                    'transaction' => $transactionId,
                    'updated_at' => Carbon::now()->setTimezone('America/Bogota'),
                ]);

            if ($claimed === 0) {
                return; // Ya fue procesada por otra petición.
            }

            // Registrar el método de pago real reportado por la pasarela (PSE/Nequi/tarjeta/...).
            $methodId = $this->paymentMethodIdFor($paymentMethodType);
            if ($methodId) {
                Order::where('id', $order->id)->update(['method_id' => $methodId]);
            }

            $order->refresh();

            $this->createInscriptions($order);
            try {
                Mail::send(new ApprovedMails($order));
            } catch (\Throwable $e) {
                Log::error('Fallo al encolar correo de orden aprobada', ['order' => $order->slack, 'error' => $e->getMessage()]);
            }

        } elseif ($status === 'PENDING') {
            $order->condition_id = 2;
            $order->save();
            try {
                Mail::send(new PendingMails($order));
            } catch (\Throwable $e) {
                Log::error('Fallo al encolar correo de orden pendiente', ['order' => $order->slack, 'error' => $e->getMessage()]);
            }

        } elseif (in_array($status, ['VOIDED', 'DECLINED', 'ERROR'])) {
            $order->condition_id = 3;
            $order->save();
            try {
                Mail::send(new VoidedMails($order));
            } catch (\Throwable $e) {
                Log::error('Fallo al encolar correo de orden rechazada', ['order' => $order->slack, 'error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Valida que un cupón sea usable AHORA por este usuario: vigencia,
     * límite total y un único uso por usuario. Pensado para invocarse dentro
     * de una transacción con el cupón bloqueado (lockForUpdate), de modo que
     * compras simultáneas no puedan superar el límite.
     */
    private function couponUsableNow($coupon, $user): bool
    {
        $date = date('Y-m-d');

        if ($coupon->start_date && $coupon->start_date > $date) {
            return false;
        }
        if ($coupon->end_date && $coupon->end_date < $date) {
            return false;
        }

        if ($coupon->limit > 0) {
            $used = CouponUsage::where('coupon_id', $coupon->id)->sum('usage_count');
            if ($used >= $coupon->limit) {
                return false;
            }
        }

        // Un uso por usuario (en checkout el usuario siempre está autenticado).
        if ($user) {
            $byUser = CouponUsage::where('coupon_id', $coupon->id)
                ->where('user_id', $user->id)
                ->sum('usage_count');
            if ($byUser >= 1) {
                return false;
            }
        }

        return true;
    }

    private function createInscriptions(Order $order): void
    {
        // Eager-load items + bundle courses to avoid N+1 queries.
        $order->loadMissing('items');

        $bundleIds = $order->items
            ->where('item_type', Bundle::class)
            ->pluck('item_id');

        $bundles = $bundleIds->isNotEmpty()
            ? Bundle::with('courses')->whereIn('id', $bundleIds)->get()->keyBy('id')
            : collect();

        // Transacción para garantizar que todas las inscripciones de un bundle
        // se crean completas o ninguna (evita inscripciones parciales ante fallo de BD).
        DB::transaction(function () use ($order, $bundles) {
            foreach ($order->items as $item) {
                if ($item->item_type === Course::class) {
                    $this->enrollCourse($order, $item->item_id);
                } elseif ($item->item_type === Bundle::class) {
                    $bundle = $bundles->get($item->item_id);
                    if ($bundle) {
                        foreach ($bundle->courses as $course) {
                            $this->enrollCourse($order, $course->id);
                        }
                    }
                }
            }
        });
    }

    private function enrollCourse(Order $order, int $courseId): void
    {
        $now = Carbon::now()->setTimezone('America/Bogota');

        $inscription = Inscription::where('user_id', $order->user_id)
            ->where('course_id', $courseId)
            ->first();

        // Renovación: el usuario ya tenía el curso -> se re-otorga el acceso
        // y se extiende la validez del certificado un año más.
        if ($inscription) {
            $inscription->enroll_start = $now;
            $inscription->enroll_expire = $now->copy()->addMonths(3);
            $inscription->expire = 0;
            $inscription->order_id = $order->id;
            $inscription->updated_at = $now;
            $inscription->save();

            $certificate = Certificate::where('user_id', $order->user_id)
                ->where('course_id', $courseId)
                ->latest('id')
                ->first();

            if ($certificate) {
                $current = $certificate->end_at ? Carbon::parse($certificate->end_at) : $now;
                $base = $current->isFuture() ? $current : $now;
                $certificate->start_at = $now;
                $certificate->end_at = $base->copy()->addYear();
                $certificate->save();
            }

            return;
        }

        $inscription = new Inscription;
        $inscription->slack = $this->generate_slack('inscriptions');
        $inscription->order_id = $order->id;
        $inscription->user_id = $order->user_id;
        $inscription->course_id = $courseId;
        $inscription->percent = 0;
        $inscription->enroll_start = $now;
        $inscription->enroll_expire = $now->copy()->addMonths(3);
        $inscription->enroll_culminated = null;
        $inscription->culminated = 0;
        $inscription->created_at = $now;
        $inscription->updated_at = $now;
        $inscription->save();

        // Notificar la matrícula (correo de bienvenida al alumno + reporte), igual
        // que el flujo de InscriptionService. El listener está en cola.
        InscriptionCreated::dispatch($inscription);
    }

    public function status($slack, $status)
    {
        seo()->noindex(true);

        $order = Order::slack($slack);

        if (! $order instanceof Order) {
            return redirect()->route('index');
        }

        // Si se perdió la sesión en el redirect de la pasarela, mandar a login
        // (guardando el destino) en vez de un 403 duro. El pago ya se procesó.
        if (! auth()->check()) {
            return redirect()->guest(route('login'));
        }

        // Solo el dueño de la orden puede ver su página de confirmación.
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $data = ['user' => $order->user, 'order' => $order, 'total' => $order->total];

        return match ($status) {
            'APPROVED' => view('pages.views.payments.approved', $data),
            'PENDING' => view('pages.views.payments.pending', $data),
            'DECLINED', 'VOIDED',
            'ERROR' => view('pages.views.payments.voided', $data),
            default => redirect()->route('index'),
        };

    }

    // apply coupon
    public function applyCoupon(Request $request)
    {

        return checkCouponValidityForCart($request->code);

    }

    private function couponApplyFailed(string $message = '', bool $success = false): array
    {
        return [
            'success' => $success,
            'message' => $message,
        ];
    }

    public function clearCoupon()
    {
        removeCoupon();

        return $this->couponApplyFailed('El cupón ha sido eliminado', false);
    }

    public function generate(Request $request)
    {
        seo()->noindex(true);

        $user = User::auth();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Tu sesión expiró. Vuelve a ingresar tus datos.',
            ], 422);
        }

        [$lines, $subtotal] = cartCheckoutLines();

        if (empty($lines)) {
            return response()->json(['success' => false, 'message' => 'Tu carrito está vacío.'], 422);
        }

        $typeorder = OrderType::slug('online');
        $method = OrderMethod::slug('card');

        $couponCode = getCoupon();
        $now = Carbon::now()->setTimezone('America/Bogota');

        $order = DB::transaction(function () use ($lines, $user, $typeorder, $method, $subtotal, $couponCode, $now) {

            // Cupón: se re-valida y consume DENTRO de la transacción con bloqueo de fila.
            // Así dos compras simultáneas no pueden exceder el límite del cupón.
            $discount = 0;
            $couponModel = null;
            if ($couponCode) {
                $couponModel = Coupon::where('code', $couponCode)->lockForUpdate()->first();
                // Re-validar en el consumo: vigencia/límite/uso + importe mínimo sobre el
                // subtotal actual (el carrito pudo cambiar tras aplicar el cupón).
                if ($couponModel
                    && $this->couponUsableNow($couponModel, $user)
                    && $subtotal >= (float) $couponModel->min_price) {
                    $discount = cartCouponDiscount($lines, $couponModel);
                    if ($discount <= 0) {
                        $couponModel = null;
                    }
                } else {
                    $couponModel = null;
                }
            }

            $total = max(0, $subtotal - $discount);
            $isFree = $total <= 0;
            $condition = OrderCondition::slug($isFree ? 'payment' : 'generada');

            // lockForUpdate serializa la asignación del número entre transacciones
            // concurrentes (evita números de orden duplicados bajo carga).
            $number = (DB::table('orders')->lockForUpdate()->max('number') ?? 0) + 1;

            $order = new Order;
            $order->slack = $this->generate_slack('orders');
            $order->number = $number;
            $order->reference = 'FAC'.$number;
            $order->user_id = $user->id;
            $order->type_id = $typeorder->id;
            $order->method_id = $method->id;
            $order->condition_id = $condition->id;
            $order->total_before_discount = $subtotal;
            $order->total_discount_amount = $discount;
            $order->total_tax_amount = 0;
            $order->total_order_amount = $total;
            $order->transaction = $isFree ? 'FREE' : null;
            $order->payment_at = $isFree ? $now : null;
            $order->coupon_id = $couponModel?->id;
            $order->created_at = $now;
            $order->updated_at = $now;
            $order->save();

            foreach ($lines as $line) {
                $orderItem = new OrderItem;
                $orderItem->slack = $this->generate_slack('order_items');
                $orderItem->order_id = $order->id;
                $orderItem->item_id = $line['id'];
                $orderItem->item_type = $line['type'] === 'course' ? Course::class : Bundle::class;
                $orderItem->quantity = $line['qty'];
                $orderItem->amount = $line['amount'];
                $orderItem->created_at = $now;
                $orderItem->updated_at = $now;
                $orderItem->save();
            }

            if ($couponModel != null) {
                // El conteo de uso se lleva en coupon_usages (validado contra coupon->limit).
                $couponUsage = new CouponUsage;
                $couponUsage->coupon_id = $couponModel->id;
                $couponUsage->user_id = $user->id;
                $couponUsage->usage_count = 1;
                $couponUsage->created_at = $now;
                $couponUsage->updated_at = $now;
                $couponUsage->save();
            }

            return $order;
        });

        // El cupón ya se consumió (o se descartó): se limpia de la sesión.
        removeCoupon();

        $isFree = $order->total_order_amount <= 0;

        // El carrito ya se materializó en la orden: lo vaciamos.
        session()->forget('cart');

        // ----- Orden gratuita: inscribir directamente, sin pasarela -----
        if ($isFree) {
            $this->createInscriptions($order);
            try {
                Mail::send(new ApprovedMails($order));
            } catch (\Throwable $e) {
                Log::error('Fallo al encolar correo de orden gratuita', ['order' => $order->slack, 'error' => $e->getMessage()]);
            }

            return response()->json([
                'success' => true,
                'free' => true,
                'redirect' => route('payments.status', [$order->slack, 'APPROVED']),
            ]);
        }

        // ----- Orden con costo: página de pago con ambas opciones (Widget + Web Checkout) -----
        return response()->json([
            'success' => true,
            'free' => false,
            'redirect' => route('payments.pay', $order->slack),
        ]);

    }

    /**
     * Página de pago de una orden: ofrece el Widget embebido y el Web Checkout de Wompi
     * (y, en sandbox, botones para simular el resultado).
     */
    public function pay(string $slack)
    {
        $order = Order::slack($slack);

        if (! $order instanceof Order || ! auth()->check() || $order->user_id !== auth()->id()) {
            abort(404);
        }

        if ($order->condition_id === 4) {
            return redirect()->route('payments.status', [$order->slack, 'APPROVED']);
        }

        $user = $order->user;
        $service = new WompiService;

        $wompi = new Wompi($order->total_order_amount, $order->slack, $user);

        $checkoutUrl = $service->checkoutUrl(
            $order->slack,
            (int) round($order->total_order_amount * 100),
            [
                'email' => $user->email,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'cellphone' => $user->cellphone,
            ],
            route('payments.response'),
        );

        return view('pages.views.payments.pay', [
            'order' => $order,
            'wompi' => $wompi,
            'checkoutUrl' => $checkoutUrl,
            'sandbox' => $service->isSandbox(),
        ]);
    }

    public static function cities(Request $request)
    {
        $term = trim((string) $request->term);

        $query = Citie::with('state.countrie')
            ->whereHas('state.countrie', fn ($q) => $q->where('title', 'Colombia'))
            ->orderBy('title');

        if ($term !== '') {
            $query->where('title', 'like', $term.'%');
        }

        // Sin término: muestra una lista inicial al abrir el select.
        $cities = $query->limit(20)->get();

        // Mostrar el departamento para distinguir ciudades homónimas (ej. Albán, Cundinamarca vs Albán, Nariño).
        $formatted_tags = $cities->map(function ($citie) {
            $state = trim(str_ireplace('Department', '', (string) optional($citie->state)->title));

            return [
                'id' => $citie->id,
                'text' => $citie->title.($state !== '' ? ', '.$state : ''),
            ];
        })->all();

        return \Response::json($formatted_tags);
    }

    protected function guard()
    {
        return Auth::guard();
    }
}
