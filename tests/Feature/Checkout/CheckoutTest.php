<?php

namespace Tests\Feature\Checkout;

use App\Models\Course\Course;
use App\Models\Order\Order;
use App\Models\Order\OrderCondition;
use App\Models\Order\OrderMethod;
use App\Models\Order\OrderType;
use App\Models\User;
use App\Services\WompiService;
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

        // Simulate a Wompi webhook with an APPROVED transaction.
        // WompiService::verifyWebhookSignature returns true when eventsSecret is empty.
        $payload = [
            'event' => 'transaction.updated',
            'data' => [
                'transaction' => [
                    'id' => 'txn-approved-001',
                    'reference' => $order->slack,
                    'status' => 'APPROVED',
                    'amount_in_cents' => 5000000,
                    'currency' => 'COP',
                ],
            ],
            'signature' => [
                'checksum' => '',
            ],
        ];

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

        $payload = [
            'event' => 'transaction.updated',
            'data' => [
                'transaction' => [
                    'id' => 'txn-declined-001',
                    'reference' => $order->slack,
                    'status' => 'DECLINED',
                    'amount_in_cents' => 2000000,
                    'currency' => 'COP',
                ],
            ],
            'signature' => ['checksum' => ''],
        ];

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
            ['X-Wompi-Signature' => 'bad-signature']
        )->assertStatus(401);
    }

    public function test_apply_coupon_returns_error_for_unknown_code(): void
    {
        $course = $this->makeCourse();

        $this->withSession(['cart' => $this->cartWithCourse($course)])
            ->postJson(route('checkout.coupon.apply'), ['code' => 'DOES-NOT-EXIST'])
            ->assertOk()
            ->assertJsonPath('success', false);
    }
}
