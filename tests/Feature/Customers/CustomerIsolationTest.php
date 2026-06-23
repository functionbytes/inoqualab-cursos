<?php

namespace Tests\Feature\Customers;

use App\Models\Course\Course;
use App\Models\Order\Order;
use App\Models\Order\OrderCondition;
use App\Models\Order\OrderMethod;
use App\Models\Order\OrderType;
use App\Models\User;
use App\Models\Users\Certificate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Blinda el aislamiento entre clientes del portal Customers/: un cliente NO
 * puede ver órdenes ni certificados de otro (los controllers resuelven con
 * ->where('user_id', $user->id)->firstOrFail(), por lo que el acceso cruzado
 * debe dar 404). Regresión que protege el scoping ante futuros refactors.
 */
class CustomerIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function seedLookups(): void
    {
        OrderType::firstOrCreate(['slug' => 'online'], ['slack' => 'ot-online', 'title' => 'Online']);
        OrderMethod::firstOrCreate(['slug' => 'card'], ['slack' => 'om-card', 'title' => 'Tarjeta']);
        foreach ([1 => 'generada', 2 => 'pendiente', 3 => 'rechazada', 4 => 'payment'] as $id => $slug) {
            DB::table('order_condition')->insertOrIgnore(['id' => $id, 'slack' => 'oc-'.$slug, 'title' => ucfirst($slug), 'slug' => $slug]);
        }
    }

    private function customer(): User
    {
        return User::factory()->create(['role' => 'customer', 'available' => 1, 'validation' => 1]);
    }

    private function orderFor(User $user, string $slack): Order
    {
        return Order::create([
            'slack' => $slack,
            'number' => random_int(1000, 99999),
            'reference' => 'FAC-'.$slack,
            'user_id' => $user->id,
            'type_id' => OrderType::where('slug', 'online')->first()->id,
            'method_id' => OrderMethod::where('slug', 'card')->first()->id,
            'condition_id' => OrderCondition::where('slug', 'payment')->first()->id,
            'total_before_discount' => 50000,
            'total_discount_amount' => 0,
            'total_tax_amount' => 0,
            'total_order_amount' => 50000,
        ]);
    }

    public function test_customer_can_view_own_order_but_not_another_customers(): void
    {
        $this->seedLookups();

        $owner = $this->customer();
        $intruder = $this->customer();
        $order = $this->orderFor($owner, 'iso-order-1');

        // Propio → 200.
        $this->actingAs($owner)
            ->get(route('customers.orders.view', $order->slack))
            ->assertOk();

        // Ajeno → 404 (no se filtra ni la existencia).
        $this->actingAs($intruder)
            ->get(route('customers.orders.view', $order->slack))
            ->assertNotFound();
    }

    public function test_customer_cannot_view_another_customers_certificate(): void
    {
        $owner = $this->customer();
        $intruder = $this->customer();
        $course = Course::factory()->create(['available' => 1]);

        $certificate = Certificate::create([
            'slack' => 'iso-cert-1',
            'user_id' => $owner->id,
            'course_id' => $course->id,
        ]);

        // Acceso cruzado al certificado de otro → 404 (firstOrFail antes de render).
        $this->actingAs($intruder)
            ->get(route('customers.certificate.view', $certificate->slack))
            ->assertNotFound();
    }
}
