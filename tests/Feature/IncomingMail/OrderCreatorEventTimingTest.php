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
 * Regresión: OrderCreator::createFromPayload() despachaba InscriptionCreated
 * DENTRO del foreach de cursos -- antes de que order->total_order_amount
 * terminara de calcularse (eso pasa después del foreach completo) y dentro
 * de la transacción abierta. Con una orden de 2+ cursos, cualquier listener
 * síncrono reaccionando al primer evento veía el total todavía en 0. Se
 * movió el dispatch a después de que la transacción confirma, con el total
 * ya final -- mismo patrón ya correcto en InscriptionService::enroll().
 *
 * Deliberadamente NO usa Event::fake(): necesita un listener real registrado
 * para capturar qué ve la BD en el momento exacto del dispatch.
 */
class OrderCreatorEventTimingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        OrderType::firstOrCreate(['slug' => 'services'], ['slack' => 'ot', 'title' => 'Servicios', 'slug' => 'services']);
        OrderMethod::firstOrCreate(['slug' => 'credit'], ['slack' => 'om', 'title' => 'Crédito', 'slug' => 'credit']);
        OrderCondition::firstOrCreate(['slug' => 'payment'], ['slack' => 'oc', 'title' => 'Pagada', 'slug' => 'payment']);
    }

    public function test_listener_sees_the_final_order_total_not_a_partial_one(): void
    {
        $distributor = Distributor::factory()->create();
        $enterprise = Enterprise::factory()->create();
        $distributor->enterprises()->attach($enterprise->id);
        $courseA = Course::factory()->create();
        $courseB = Course::factory()->create();
        DistributorCourse::create(['distributor_id' => $distributor->id, 'course_id' => $courseA->id, 'price' => 50000]);
        DistributorCourse::create(['distributor_id' => $distributor->id, 'course_id' => $courseB->id, 'price' => 30000]);
        $mail = IncomingMail::factory()->create();

        $totalsSeenAtDispatch = [];
        Event::listen(InscriptionCreated::class, function (InscriptionCreated $event) use (&$totalsSeenAtDispatch) {
            $totalsSeenAtDispatch[] = (float) Order::find($event->inscription->order_id)->total_order_amount;
        });

        $payload = ['document' => '123456789', 'firstname' => 'Ana', 'lastname' => 'Gómez', 'name' => 'Ana Gómez'];
        $order = (new OrderCreator)->createFromPayload($mail, $payload, $enterprise, [$courseA, $courseB]);

        $this->assertCount(2, $totalsSeenAtDispatch);
        // Ambos listeners vieron el total YA final (80000), no 0 a medio calcular.
        $this->assertSame([80000.0, 80000.0], $totalsSeenAtDispatch);
        $this->assertSame(80000.0, (float) $order->total_order_amount);
    }
}
