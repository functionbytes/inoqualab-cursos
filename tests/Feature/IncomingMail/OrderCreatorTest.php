<?php

namespace Tests\Feature\IncomingMail;

use App\Events\Inscriptions\InscriptionCreated;
use App\Models\Course\Course;
use App\Models\Distributor\Distributor;
use App\Models\Distributor\DistributorCourse;
use App\Models\Enterprise\Enterprise;
use App\Models\Mail\IncomingMail;
use App\Models\Order\Order;
use App\Models\Order\OrderCondition;
use App\Models\Order\OrderMethod;
use App\Models\Order\OrderType;
use App\Services\IncomingMail\OrderCreator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * El creador de órdenes desde correos entrantes crea la orden con la tarifa del
 * distribuidor; si un curso no tiene tarifa asignada, debe FALLAR (para que el
 * correo vaya a revisión manual) en vez de crear una orden con monto incorrecto.
 */
class OrderCreatorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        OrderType::firstOrCreate(['slug' => 'services'], ['slack' => 'ot', 'title' => 'Servicios', 'slug' => 'services']);
        OrderMethod::firstOrCreate(['slug' => 'credit'], ['slack' => 'om', 'title' => 'Crédito', 'slug' => 'credit']);
        OrderCondition::firstOrCreate(['slug' => 'payment'], ['slack' => 'oc', 'title' => 'Pagada', 'slug' => 'payment']);
        Event::fake([InscriptionCreated::class]);
    }

    /** @return array{enterprise:Enterprise, distributor:Distributor, course:Course} */
    private function scenario(): array
    {
        $distributor = Distributor::factory()->create();
        $enterprise = Enterprise::factory()->create();
        $distributor->enterprises()->attach($enterprise->id);
        $course = Course::factory()->create();

        return compact('enterprise', 'distributor', 'course');
    }

    private function payload(): array
    {
        return ['document' => '123456789', 'firstname' => 'Ana', 'lastname' => 'Gómez', 'name' => 'Ana Gómez'];
    }

    public function test_creates_order_with_distributor_tariff(): void
    {
        ['enterprise' => $e, 'distributor' => $d, 'course' => $c] = $this->scenario();
        DistributorCourse::create(['distributor_id' => $d->id, 'course_id' => $c->id, 'price' => 80000]);
        $mail = IncomingMail::factory()->create();

        $order = (new OrderCreator)->createFromPayload($mail, $this->payload(), $e, [$c]);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertSame(80000.0, (float) $order->total_order_amount);
        $this->assertDatabaseHas('order_items', ['order_id' => $order->id, 'item_id' => $c->id, 'amount' => 80000]);
        $this->assertDatabaseHas('inscriptions', ['user_id' => $order->user_id, 'course_id' => $c->id]);
    }

    public function test_fails_when_course_has_no_tariff(): void
    {
        ['enterprise' => $e, 'course' => $c] = $this->scenario();
        // Sin DistributorCourse → sin tarifa.
        $mail = IncomingMail::factory()->create();

        try {
            (new OrderCreator)->createFromPayload($mail, $this->payload(), $e, [$c]);
            $this->fail('Debía lanzar excepción por curso sin tarifa.');
        } catch (\RuntimeException $ex) {
            $this->assertStringContainsString('sin tarifa', $ex->getMessage());
        }

        // La transacción hizo rollback: no quedó orden ni inscripción huérfana.
        $this->assertSame(0, Order::count());
        $this->assertDatabaseMissing('inscriptions', ['course_id' => $c->id]);
    }
}
