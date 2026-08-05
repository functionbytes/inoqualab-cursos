<?php

namespace Tests\Feature\Console;

use App\Models\Course\Course;
use App\Models\Distributor\Distributor;
use App\Models\Enterprise\Enterprise;
use App\Models\Invoice\InvoiceCondition;
use App\Models\Invoice\InvoiceMethod;
use App\Models\Order\Order;
use App\Models\Order\OrderActivity;
use App\Models\Order\OrderCondition;
use App\Models\Order\OrderItem;
use App\Models\Order\OrderMethod;
use App\Models\Order\OrderType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Regresión de invoices:generate (comando mensual desatendido). Dos bugs
 * reales encontrados en la misma auditoría:
 *
 * 1. $distributor->orders() devuelve OrderActivity -- una fila POR CURSO
 *    matriculado en una orden. Sin deduplicar por order->id, una orden con
 *    varios cursos sumaba su total_order_amount una vez por cada curso,
 *    inflando la factura. El flujo manual equivalente
 *    (Managers\Invoices\InvoicesController) ya deduplicaba; el comando no.
 * 2. invoice_items no tiene columna 'amount' (solo subtotal/total): asignar
 *    ->amount y guardar tiraba un QueryException real en cada ejecución con
 *    ítems, sin ninguna alerta salvo el log del scheduler.
 */
class GenerateDistributorInvoicesTest extends TestCase
{
    use RefreshDatabase;

    private function seedCatalogs(): void
    {
        InvoiceCondition::firstOrCreate(['slug' => 'generada'], ['slack' => 'ic-generada', 'title' => 'Generada', 'slug' => 'generada']);
        InvoiceMethod::firstOrCreate(['slug' => 'credit'], ['slack' => 'im-credit', 'title' => 'Crédito', 'slug' => 'credit']);
        OrderType::firstOrCreate(['slug' => 'online'], ['slack' => 'ot-online', 'title' => 'Online', 'slug' => 'online']);
        OrderMethod::firstOrCreate(['slug' => 'card'], ['slack' => 'om-card', 'title' => 'Tarjeta', 'slug' => 'card']);
        OrderCondition::firstOrCreate(['slug' => 'payment'], ['slack' => 'oc-payment', 'title' => 'Pagada', 'slug' => 'payment']);
    }

    private function orderCatalogIds(): array
    {
        return [
            OrderType::where('slug', 'online')->value('id'),
            OrderMethod::where('slug', 'card')->value('id'),
            OrderCondition::where('slug', 'payment')->value('id'),
        ];
    }

    public function test_order_with_multiple_courses_is_invoiced_once_not_multiplied(): void
    {
        $this->seedCatalogs();

        $distributor = Distributor::factory()->create();
        $enterprise = Enterprise::factory()->create();
        $customer = User::factory()->create(['role' => 'customer']);
        $courseA = Course::factory()->create();
        $courseB = Course::factory()->create();

        [$typeId, $methodId, $conditionId] = $this->orderCatalogIds();

        // Una orden con DOS cursos: total real = 100000, no 200000.
        $order = Order::create([
            'slack' => 'ord-'.uniqid(),
            'number' => (Order::max('number') ?? 0) + 1,
            'reference' => 'ORD-'.uniqid(),
            'user_id' => $customer->id,
            'type_id' => $typeId,
            'method_id' => $methodId,
            'condition_id' => $conditionId,
            'total_before_discount' => 100000,
            'total_discount_amount' => 0,
            'total_tax_amount' => 0,
            'total_order_amount' => 100000,
        ]);

        OrderItem::create([
            'slack' => 'oi-'.uniqid(),
            'order_id' => $order->id,
            'item_id' => $courseA->id,
            'item_type' => Course::class,
            'quantity' => 1,
            'amount' => 60000,
        ]);
        OrderItem::create([
            'slack' => 'oi-'.uniqid(),
            'order_id' => $order->id,
            'item_id' => $courseB->id,
            'item_type' => Course::class,
            'quantity' => 1,
            'amount' => 40000,
        ]);

        // Dos OrderActivity para la MISMA orden (una por curso matriculado) --
        // esto es justo lo que antes hacía que el total se sumara x2.
        foreach ([$courseA, $courseB] as $course) {
            OrderActivity::create([
                'slack' => Str::random(8),
                'order_id' => $order->id,
                'course_id' => $course->id,
                'distributor_id' => $distributor->id,
                'enterprise_id' => $enterprise->id,
                'item_type' => Distributor::class,
                'id_type' => $distributor->id,
                'relation_id' => 0,
                'invoiced' => 0,
            ]);
        }

        $this->artisan('invoices:generate')->assertSuccessful();

        // Una sola factura, con el total real de la orden (no x2).
        $this->assertDatabaseCount('invoices', 1);
        $this->assertDatabaseHas('invoices', [
            'distributor_id' => $distributor->id,
            'total_invoices_amount' => 100000,
        ]);

        // Ambas actividades quedan marcadas como facturadas (no solo la primera).
        $this->assertSame(0, OrderActivity::where('order_id', $order->id)->where('invoiced', 0)->count());

        // invoice_items se creó sin error (subtotal/total, no la columna
        // inexistente 'amount') con el desglose por curso, no duplicado.
        $this->assertDatabaseHas('invoice_items', ['course_id' => $courseA->id, 'total' => 60000]);
        $this->assertDatabaseHas('invoice_items', ['course_id' => $courseB->id, 'total' => 40000]);
    }

    public function test_command_does_not_regenerate_invoice_for_already_invoiced_activity(): void
    {
        $this->seedCatalogs();

        $distributor = Distributor::factory()->create();
        $enterprise = Enterprise::factory()->create();
        $customer = User::factory()->create(['role' => 'customer']);
        $course = Course::factory()->create();

        [$typeId, $methodId, $conditionId] = $this->orderCatalogIds();

        $order = Order::create([
            'slack' => 'ord-'.uniqid(),
            'number' => (Order::max('number') ?? 0) + 1,
            'reference' => 'ORD-'.uniqid(),
            'user_id' => $customer->id,
            'type_id' => $typeId,
            'method_id' => $methodId,
            'condition_id' => $conditionId,
            'total_before_discount' => 50000,
            'total_discount_amount' => 0,
            'total_tax_amount' => 0,
            'total_order_amount' => 50000,
        ]);

        OrderItem::create([
            'slack' => 'oi-'.uniqid(),
            'order_id' => $order->id,
            'item_id' => $course->id,
            'item_type' => Course::class,
            'quantity' => 1,
            'amount' => 50000,
        ]);

        OrderActivity::create([
            'slack' => Str::random(8),
            'order_id' => $order->id,
            'course_id' => $course->id,
            'distributor_id' => $distributor->id,
            'enterprise_id' => $enterprise->id,
            'item_type' => Distributor::class,
            'id_type' => $distributor->id,
            'relation_id' => 0,
            'invoiced' => 0,
        ]);

        $this->artisan('invoices:generate')->assertSuccessful();
        $this->assertDatabaseCount('invoices', 1);

        // Segunda corrida del mismo mes: sin esto, scopeDate() (invoiced = 0)
        // volvía a traer la misma actividad y generaba una segunda factura
        // duplicada para lo mismo.
        $this->artisan('invoices:generate')->assertSuccessful();
        $this->assertDatabaseCount('invoices', 1);
    }
}
