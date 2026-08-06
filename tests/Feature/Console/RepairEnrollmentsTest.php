<?php

namespace Tests\Feature\Console;

use App\Enums\OrderCondition;
use App\Models\Bundle\Bundle;
use App\Models\Course\Course;
use App\Models\Inscription;
use App\Models\Order\Order;
use App\Models\Order\OrderItem;
use App\Models\Order\OrderMethod;
use App\Models\Order\OrderType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Regresión de orders:repair-enrollments (red de seguridad del webhook).
 *
 * El chequeo original excluía cualquier orden con AL MENOS UNA inscripción
 * propia (`whereNotExists` de inscriptions.order_id = orders.id). Una orden
 * con un curso ya matriculado y un bundle sin matricular (fallo parcial, o
 * un bundle borrado físicamente -- Bundle no tiene SoftDeletes) escapaba al
 * repair para siempre, porque "tiene una inscripción" ya la descartaba.
 */
class RepairEnrollmentsTest extends TestCase
{
    use RefreshDatabase;

    protected function seedLookups(): void
    {
        OrderType::firstOrCreate(['slug' => 'online'], ['slack' => 'ot-online', 'title' => 'Online', 'slug' => 'online']);
        OrderMethod::firstOrCreate(['slug' => 'card'], ['slack' => 'om-card', 'title' => 'Tarjeta', 'slug' => 'card']);

        foreach ([1 => 'generada', 2 => 'pendiente', 3 => 'rechazada', 4 => 'payment'] as $id => $slug) {
            DB::table('order_condition')->insertOrIgnore([
                'id' => $id, 'slack' => 'oc-'.$slug, 'title' => ucfirst($slug), 'slug' => $slug,
            ]);
        }
    }

    private function makePaidOrder(User $user): Order
    {
        return Order::create([
            'slack' => 'ord-'.uniqid(),
            'number' => (Order::max('number') ?? 0) + 1,
            'reference' => 'ORD-'.uniqid(),
            'user_id' => $user->id,
            'type_id' => OrderType::where('slug', 'online')->value('id'),
            'method_id' => OrderMethod::where('slug', 'card')->value('id'),
            'condition_id' => OrderCondition::Pagada->value,
            'payment_at' => Carbon::now()->subHour(),
            'total_before_discount' => 100000,
            'total_discount_amount' => 0,
            'total_tax_amount' => 0,
            'total_order_amount' => 100000,
        ]);
    }

    public function test_repairs_missing_bundle_courses_when_order_already_has_a_partial_inscription(): void
    {
        $this->seedLookups();

        $user = User::factory()->create(['role' => 'customer']);
        $courseAlreadyOk = Course::factory()->create();
        $bundleCourseA = Course::factory()->create();
        $bundleCourseB = Course::factory()->create();

        $bundle = Bundle::create([
            'slack' => 'bundle-'.uniqid(),
            'title' => 'Paquete de prueba',
            'slug' => 'paquete-'.uniqid(),
            'price' => 100000,
            'available' => 1,
        ]);
        $bundle->courses()->attach([$bundleCourseA->id, $bundleCourseB->id]);

        $order = $this->makePaidOrder($user);

        OrderItem::create([
            'slack' => 'oi-'.uniqid(),
            'order_id' => $order->id,
            'item_id' => $courseAlreadyOk->id,
            'item_type' => Course::class,
            'quantity' => 1,
            'amount' => 40000,
        ]);
        OrderItem::create([
            'slack' => 'oi-'.uniqid(),
            'order_id' => $order->id,
            'item_id' => $bundle->id,
            'item_type' => Bundle::class,
            'quantity' => 1,
            'amount' => 60000,
        ]);

        // Simula el fallo parcial: el curso directo SÍ quedó matriculado (por
        // esta misma orden), pero el bundle no -- el webhook falló a mitad
        // de camino. Con el chequeo viejo (whereNotExists de CUALQUIER
        // inscripción), esta orden ya no era candidata a repair nunca más.
        $enrolledAt = Carbon::now()->subDays(3);
        $existing = Inscription::create([
            'slack' => 'insc-'.uniqid(),
            'order_id' => $order->id,
            'user_id' => $user->id,
            'course_id' => $courseAlreadyOk->id,
            'percent' => 0,
            'enroll_start' => $enrolledAt,
            'enroll_expire' => $enrolledAt->copy()->addMonths(3),
            'culminated' => 0,
        ]);
        $enrollStartBefore = $existing->fresh()->getRawOriginal('enroll_start');

        $this->artisan('orders:repair-enrollments')->assertSuccessful();

        // Los dos cursos del bundle quedan matriculados.
        $this->assertDatabaseHas('inscriptions', ['user_id' => $user->id, 'course_id' => $bundleCourseA->id]);
        $this->assertDatabaseHas('inscriptions', ['user_id' => $user->id, 'course_id' => $bundleCourseB->id]);

        // El curso que YA estaba bien matriculado no se toca -- repararlo de
        // más lo re-"renovaría" (enroll_start a hoy), pisando datos válidos.
        $this->assertSame($enrollStartBefore, $existing->fresh()->getRawOriginal('enroll_start'));
    }

    public function test_does_not_touch_orders_that_are_already_fully_enrolled(): void
    {
        $this->seedLookups();

        $user = User::factory()->create(['role' => 'customer']);
        $course = Course::factory()->create();
        $order = $this->makePaidOrder($user);

        OrderItem::create([
            'slack' => 'oi-'.uniqid(),
            'order_id' => $order->id,
            'item_id' => $course->id,
            'item_type' => Course::class,
            'quantity' => 1,
            'amount' => 100000,
        ]);

        $enrolledAt = Carbon::now()->subDays(3);
        Inscription::create([
            'slack' => 'insc-'.uniqid(),
            'order_id' => $order->id,
            'user_id' => $user->id,
            'course_id' => $course->id,
            'percent' => 0,
            'enroll_start' => $enrolledAt,
            'enroll_expire' => $enrolledAt->copy()->addMonths(3),
            'culminated' => 0,
        ]);

        $this->artisan('orders:repair-enrollments')
            ->expectsOutputToContain('reparadas: 0')
            ->assertSuccessful();

        $this->assertSame(1, Inscription::where('user_id', $user->id)->where('course_id', $course->id)->count());
    }
}
