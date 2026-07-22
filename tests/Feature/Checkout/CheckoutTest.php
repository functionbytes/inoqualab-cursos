<?php

namespace Tests\Feature\Checkout;

use App\Http\Controllers\Pages\CheckoutController;
use App\Mail\Customers\Orders\PendingMails;
use App\Mail\Customers\Orders\VoidedMails;
use App\Model\Wompi;
use App\Models\Course\Course;
use App\Models\Inscription;
use App\Models\Order\Order;
use App\Models\Order\OrderCondition;
use App\Models\Order\OrderItem;
use App\Models\Order\OrderMethod;
use App\Models\Order\OrderType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ──────────────────────────────────────────────────────────────────

    /**
     * Seed the lookup rows every controller action expects to exist.
     * OrderType (online), OrderMethod (card), OrderCondition (generada / payment)
     * must all be present; the store controller calls ::slug() which aborts 404 if missing.
     */
    protected function seedLookups(): void
    {
        OrderType::firstOrCreate(['slug' => 'online'], [
            'slack' => 'ot-online',
            'title' => 'Online',
            'slug' => 'online',
        ]);

        OrderMethod::firstOrCreate(['slug' => 'card'], [
            'slack' => 'om-card',
            'title' => 'Tarjeta',
            'slug' => 'card',
        ]);

        // El webhook mapea estados a condition_id por número:
        // 1=generada, 2=pendiente, 3=rechazada, 4=payment. Sembramos las 4
        // con ids explícitos para que las transiciones del webhook no violen
        // la FK orders.condition_id -> order_condition.id.
        foreach ([1 => 'generada', 2 => 'pendiente', 3 => 'rechazada', 4 => 'payment'] as $id => $slug) {
            \DB::table('order_condition')->insertOrIgnore([
                'id' => $id,
                'slack' => 'oc-'.$slug,
                'title' => ucfirst($slug),
                'slug' => $slug,
            ]);
        }
    }

    private function makeUser(string $role = 'customer'): User
    {
        return User::factory()->create([
            'role' => $role,
            'available' => 1,
            'validation' => 1,
        ]);
    }

    private function makeCourse(float $price = 100000): Course
    {
        return Course::factory()->create([
            'price' => $price,
            'payment' => 1,
            'promotion' => 0,
            'available' => 1,
        ]);
    }

    private function cartWithCourse(Course $course): array
    {
        return [
            'course_'.$course->slack => [
                'type' => 'course',
                'slack' => $course->slack,
                'title' => $course->title,
                'price' => $course->price,
                'qty' => 1,
            ],
        ];
    }

    // ── Tests ─────────────────────────────────────────────────────────────────────

    public function test_guest_can_view_checkout_page_with_items_in_cart(): void
    {
        $course = $this->makeCourse();

        $this->withSession(['cart' => $this->cartWithCourse($course)])
            ->get(route('checkout.cart'))
            ->assertOk();
    }

    public function test_checkout_page_redirects_to_cart_when_cart_is_empty(): void
    {
        $this->withSession(['cart' => []])
            ->get(route('checkout.cart'))
            ->assertRedirect(route('cart.index'));
    }

    public function test_authenticated_user_can_generate_paid_order(): void
    {
        Mail::fake();
        $this->seedLookups();

        $user = $this->makeUser();
        $course = $this->makeCourse(50000);

        $response = $this->actingAs($user)
            ->withSession(['cart' => $this->cartWithCourse($course)])
            ->postJson(route('checkout.generate'));

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total_order_amount' => 50000,
        ]);
    }

    public function test_generate_returns_422_when_cart_is_empty(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)
            ->withSession(['cart' => []])
            ->postJson(route('checkout.generate'))
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_generate_returns_422_for_unauthenticated_user(): void
    {
        $this->withSession(['cart' => []])
            ->postJson(route('checkout.generate'))
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_wompi_webhook_processes_approved_payment(): void
    {
        Mail::fake();
        $this->seedLookups();

        $user = $this->makeUser();
        $course = $this->makeCourse(50000);

        // Create an order in "generada" condition (not yet paid).
        $condition = OrderCondition::where('slug', 'generada')->first();
        $order = Order::create([
            'slack' => 'test-order-1',
            'number' => 1,
            'reference' => 'FAC1',
            'user_id' => $user->id,
            'type_id' => OrderType::where('slug', 'online')->first()->id,
            'method_id' => OrderMethod::where('slug', 'card')->first()->id,
            'condition_id' => $condition->id,
            'total_before_discount' => 5000000,
            'total_discount_amount' => 0,
            'total_tax_amount' => 0,
            'total_order_amount' => 50000,
        ]);

        // El webhook ahora valida firma y es fail-closed: se firma el payload.
        config(['services.wompi.events_secret' => 'test-secret']);
        $payload = $this->signedWebhookPayload($order->slack, 'txn-approved-001', 'APPROVED', 5000000, 'test-secret');

        $this->postJson(route('payments.wompi.webhook'), $payload)
            ->assertOk();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'condition_id' => OrderCondition::where('slug', 'payment')->first()->id,
            'transaction' => 'txn-approved-001',
        ]);
    }

    public function test_wompi_webhook_ignores_non_approved_status(): void
    {
        Mail::fake();
        $this->seedLookups();

        $user = $this->makeUser();

        $condition = OrderCondition::where('slug', 'generada')->first();
        $order = Order::create([
            'slack' => 'test-order-2',
            'number' => 2,
            'reference' => 'FAC2',
            'user_id' => $user->id,
            'type_id' => OrderType::where('slug', 'online')->first()->id,
            'method_id' => OrderMethod::where('slug', 'card')->first()->id,
            'condition_id' => $condition->id,
            'total_before_discount' => 2000000,
            'total_discount_amount' => 0,
            'total_tax_amount' => 0,
            'total_order_amount' => 20000,
        ]);

        config(['services.wompi.events_secret' => 'test-secret']);
        $payload = $this->signedWebhookPayload($order->slack, 'txn-declined-001', 'DECLINED', 2000000, 'test-secret');

        $this->postJson(route('payments.wompi.webhook'), $payload)
            ->assertOk();

        // condition_id must NOT change to 4 (payment).
        $paidConditionId = OrderCondition::where('slug', 'payment')->first()->id;
        $this->assertDatabaseMissing('orders', [
            'id' => $order->id,
            'condition_id' => $paidConditionId,
        ]);
    }

    public function test_wompi_webhook_rejects_invalid_signature(): void
    {
        // Configure a non-empty eventsSecret so signature verification is enforced.
        config(['services.wompi.events_secret' => 'real-secret']);

        $payload = [
            'event' => 'transaction.updated',
            'data' => [
                'transaction' => [
                    'id' => 'txn-bad-sig',
                    'reference' => 'some-order',
                    'status' => 'APPROVED',
                    'amount_in_cents' => 1000000,
                    'currency' => 'COP',
                ],
            ],
            'signature' => ['checksum' => ''],
        ];

        $this->postJson(
            route('payments.wompi.webhook'),
            $payload,
            ['X-Event-Checksum' => 'bad-signature']
        )->assertStatus(401);
    }

    /**
     * Construye un payload de webhook de Wompi correctamente firmado:
     * SHA256( valores de signature.properties + timestamp + events_secret ).
     */
    private function signedWebhookPayload(string $reference, string $txnId, string $status, int $amountInCents, string $secret): array
    {
        $timestamp = 1700000000;
        $checksum = hash('sha256', $txnId.$status.$amountInCents.$timestamp.$secret);

        return [
            'event' => 'transaction.updated',
            'timestamp' => $timestamp,
            'signature' => [
                'properties' => ['transaction.id', 'transaction.status', 'transaction.amount_in_cents'],
                'checksum' => $checksum,
            ],
            'data' => [
                'transaction' => [
                    'id' => $txnId,
                    'reference' => $reference,
                    'status' => $status,
                    'amount_in_cents' => $amountInCents,
                    'currency' => 'COP',
                ],
            ],
        ];
    }

    public function test_apply_coupon_returns_error_for_unknown_code(): void
    {
        $course = $this->makeCourse();

        $this->withSession(['cart' => $this->cartWithCourse($course)])
            ->postJson(route('checkout.coupon.apply'), ['code' => 'DOES-NOT-EXIST'])
            ->assertOk()
            ->assertJsonPath('success', false);
    }

    // ── Garantías anti-fraude del webhook / checkout ───────────────────────────────

    /** Crea una orden "generada" con un ítem de curso, lista para que el webhook la pague. */
    private function makeGeneratedOrder(User $user, Course $course, string $slack, float $total): Order
    {
        $order = Order::create([
            'slack' => $slack,
            'number' => random_int(1000, 9999),
            'reference' => 'FAC-'.$slack,
            'user_id' => $user->id,
            'type_id' => OrderType::where('slug', 'online')->first()->id,
            'method_id' => OrderMethod::where('slug', 'card')->first()->id,
            'condition_id' => OrderCondition::where('slug', 'generada')->first()->id,
            'total_before_discount' => $total,
            'total_discount_amount' => 0,
            'total_tax_amount' => 0,
            'total_order_amount' => $total,
        ]);

        OrderItem::create([
            'slack' => 'oi-'.$slack,
            'order_id' => $order->id,
            'item_id' => $course->id,
            'item_type' => Course::class,
            'quantity' => 1,
            'amount' => $total,
        ]);

        return $order;
    }

    private function paidConditionId(): int
    {
        return OrderCondition::where('slug', 'payment')->first()->id;
    }

    public function test_wompi_webhook_is_idempotent_and_does_not_double_enroll(): void
    {
        Mail::fake();
        $this->seedLookups();

        $user = $this->makeUser();
        $course = $this->makeCourse(50000);
        $order = $this->makeGeneratedOrder($user, $course, 'idem-order', 50000);

        config(['services.wompi.events_secret' => 'test-secret']);
        $payload = $this->signedWebhookPayload($order->slack, 'txn-idem', 'APPROVED', 5000000, 'test-secret');

        // Mismo webhook entregado dos veces (Wompi reintenta).
        $this->postJson(route('payments.wompi.webhook'), $payload)->assertOk();
        $this->postJson(route('payments.wompi.webhook'), $payload)->assertOk();

        // La orden se paga una vez y la matrícula NO se duplica.
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'condition_id' => $this->paidConditionId()]);
        $this->assertSame(1, Inscription::where('user_id', $user->id)->where('course_id', $course->id)->count());
    }

    public function test_wompi_webhook_rejects_amount_mismatch(): void
    {
        Mail::fake();
        $this->seedLookups();

        $user = $this->makeUser();
        $course = $this->makeCourse(50000);
        $order = $this->makeGeneratedOrder($user, $course, 'amount-order', 50000);

        config(['services.wompi.events_secret' => 'test-secret']);
        // Firma válida, pero el monto (100 cents) no coincide con el total (5.000.000 cents).
        $payload = $this->signedWebhookPayload($order->slack, 'txn-bad-amount', 'APPROVED', 100, 'test-secret');

        $this->postJson(route('payments.wompi.webhook'), $payload)->assertOk();

        // No se marca pagada ni se inscribe (anti-underpayment).
        $this->assertDatabaseMissing('orders', ['id' => $order->id, 'condition_id' => $this->paidConditionId()]);
        $this->assertSame(0, Inscription::where('user_id', $user->id)->where('course_id', $course->id)->count());
    }

    public function test_wompi_webhook_rejects_non_cop_currency(): void
    {
        Mail::fake();
        $this->seedLookups();

        $user = $this->makeUser();
        $course = $this->makeCourse(50000);
        $order = $this->makeGeneratedOrder($user, $course, 'currency-order', 50000);

        config(['services.wompi.events_secret' => 'test-secret']);
        // La moneda no entra en la firma; se altera a USD manteniendo el checksum válido.
        $payload = $this->signedWebhookPayload($order->slack, 'txn-usd', 'APPROVED', 5000000, 'test-secret');
        $payload['data']['transaction']['currency'] = 'USD';

        $this->postJson(route('payments.wompi.webhook'), $payload)->assertOk();

        $this->assertDatabaseMissing('orders', ['id' => $order->id, 'condition_id' => $this->paidConditionId()]);
        $this->assertSame(0, Inscription::where('user_id', $user->id)->where('course_id', $course->id)->count());
    }

    public function test_free_order_enrolls_without_gateway(): void
    {
        Mail::fake();
        $this->seedLookups();

        $user = $this->makeUser();
        $course = $this->makeCourse(0);

        $response = $this->actingAs($user)
            ->withSession(['cart' => $this->cartWithCourse($course)])
            ->postJson(route('checkout.generate'));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('free', true);

        $order = Order::where('user_id', $user->id)->latest('id')->first();
        $this->assertNotNull($order);
        $this->assertSame(0, (int) $order->total_order_amount);
        $this->assertSame($this->paidConditionId(), $order->condition_id);
        $this->assertSame(1, Inscription::where('user_id', $user->id)->where('course_id', $course->id)->count());
    }

    /**
     * /payments/response reconsulta a Wompi en cada visita y reentra a
     * processOrderStatus sin la idempotencia del webhook (PaymentEvent):
     * el correo de "pendiente"/"rechazada" solo debe salir en la transición
     * de estado, no en cada refresh de la página.
     */
    public function test_repeated_pending_status_sends_email_only_once(): void
    {
        Mail::fake();
        $this->seedLookups();

        $user = $this->makeUser();
        $course = $this->makeCourse();
        $order = $this->makeGeneratedOrder($user, $course, 'order-pending-mail', 50000);

        $controller = app(CheckoutController::class);
        $controller->processOrderStatus($order->slack, 'txn-pending-01', 'PENDING');
        $controller->processOrderStatus($order->slack, 'txn-pending-01', 'PENDING');

        // Los mailables implementan ShouldQueue: con Mail::fake quedan en "queued".
        Mail::assertQueued(PendingMails::class, 1);

        $controller->processOrderStatus($order->slack, 'txn-pending-01', 'DECLINED');
        $controller->processOrderStatus($order->slack, 'txn-pending-01', 'DECLINED');

        Mail::assertQueued(VoidedMails::class, 1);
    }

    /**
     * El monto del widget debe redondearse al centavo: un cast directo
     * (int)(66583.33*100) trunca a 6658332 por error de flotantes y la
     * validación de monto de processOrderStatus rechazaría el pago aprobado.
     */
    public function test_wompi_widget_amount_rounds_to_nearest_cent(): void
    {
        $user = new User(['email' => 'w@test.com']);
        $user->firstname = 'Test';
        $user->lastname = 'User';

        $wompi = new Wompi(66583.33, 'ref-round', $user);

        $this->assertSame(6658333, $wompi->amount);
        $this->assertSame((int) round(66583.33 * 100), $wompi->amount);
    }
}
