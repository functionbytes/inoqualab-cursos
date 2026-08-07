<?php

namespace App\Console\Commands\Accountings;

use App\Events\Invoices\InvoiceCreated;
use App\Models\Distributor\Distributor;
use App\Models\Invoice\Invoice;
use App\Models\Invoice\InvoiceCondition;
use App\Models\Invoice\InvoiceItem;
use App\Models\Invoice\InvoiceMethod;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Invoices extends Command
{
    protected $signature = 'invoices:generate';

    protected $description = 'Genera facturas mensuales a partir de órdenes de distribuidores y proveedores';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $startDate = Carbon::now()->startOfMonth(); // Inicio del mes actual
        $endDate = Carbon::now()->endOfMonth(); // Fin del mes actual

        $distributors = Distributor::available()->get();

        // Los scopes slug() abortan con un 404 HTTP, que en consola sale como
        // NotFoundHttpException y no dice nada útil. Aquí se consulta directo
        // para poder explicar qué falta y salir con código de error.
        $condition = InvoiceCondition::where('slug', 'generada')->first();
        $method = InvoiceMethod::where('slug', 'credit')->first();

        if ($condition === null || $method === null) {
            $this->error('Falta un catálogo obligatorio para facturar:');
            if ($condition === null) {
                $this->error("  - invoice_condition con slug 'generada'");
            }
            if ($method === null) {
                $this->error("  - invoice_method con slug 'credit'");
            }

            return self::FAILURE;
        }

        foreach ($distributors as $distributor) {

            try {
                DB::transaction(function () use ($distributor, $method, $condition, $startDate, $endDate) {
                    // lockForUpdate() + fetch dentro de la transacción: si el
                    // comando se ejecuta dos veces solapadas (relanzado a mano
                    // mientras el cron mensual también corre), sin esto ambas
                    // corridas podían leer el mismo lote de OrderActivity
                    // invoiced=0 antes de que cualquiera hiciera commit, generando
                    // dos facturas duplicadas para el mismo distribuidor y periodo.
                    $items = $distributor->orders()->date($startDate, $endDate)->lockForUpdate()->get();

                    if ($items->count() === 0) {
                        return;
                    }

                    $coursesItems = [];
                    $total = 0;

                    $invoice = new Invoice;
                    $invoice->slack = $this->generate_slack('invoices');
                    $invoice->number = $this->generate_number('invoices');
                    $invoice->reference = setting('invoice_default').$invoice->number;
                    $invoice->method_id = $method->id;
                    $invoice->condition_id = $condition->id;
                    $invoice->distributor_id = $distributor->id;
                    $invoice->payment_at = null;
                    $invoice->notes = '';
                    $invoice->available = 1;
                    $invoice->from_at = $startDate;
                    $invoice->to_at = $endDate;
                    $invoice->created_at = Carbon::now()->setTimezone('America/Bogota');
                    $invoice->updated_at = Carbon::now()->setTimezone('America/Bogota');

                    // Una orden puede tener varias OrderActivity (una por curso
                    // matriculado): sin deduplicar por order->id, su total y sus
                    // ítems se sumaban una vez POR CADA curso, inflando la factura.
                    // Mismo patrón ya corregido en el flujo manual equivalente
                    // (Managers\Invoices\InvoicesController::store()).
                    $seenOrders = [];

                    foreach ($items as $item) {

                        $order = $item->order;

                        if ($order && ! isset($seenOrders[$order->id])) {
                            $seenOrders[$order->id] = true;
                            $total += $order->total_order_amount;

                            foreach ($order->items as $orderItem) {

                                $itemType = $orderItem->item_type;
                                $itemId = $orderItem->item_id;
                                // OrderItem::amount YA es el total de línea (unit * qty);
                                // no volver a multiplicar por cantidad.
                                $itemAmount = $orderItem->amount;

                                if (! isset($coursesItems[$itemType])) {
                                    $coursesItems[$itemType] = [];
                                }

                                if (! isset($coursesItems[$itemType][$itemId])) {
                                    $coursesItems[$itemType][$itemId] = [
                                        'quantity' => 0,
                                        'total_amount' => 0,
                                        'course_id' => $itemId,
                                    ];
                                }

                                $coursesItems[$itemType][$itemId]['quantity'] += $orderItem->quantity;
                                $coursesItems[$itemType][$itemId]['total_amount'] += $itemAmount;

                            }
                        }

                        // Todas las actividades se marcan como facturadas (no solo
                        // la primera de cada orden), para que scopeDate() no las
                        // vuelva a traer el próximo mes.
                        $item->invoiced = 1;
                        $item->invoiced_at = Carbon::now()->setTimezone('America/Bogota');
                        $item->save();
                    }

                    $invoice->total_discount_amount = 0;
                    $invoice->total_after_discount = $total;
                    $invoice->total_before_discount = $total;
                    $invoice->total_tax_amount = 0;
                    $invoice->total_invoices_amount = $total;
                    $invoice->save();

                    foreach ($coursesItems as $itemType => $itemsById) {

                        foreach ($itemsById as $itemId => $data) {

                            $itemInvoice = new InvoiceItem;
                            $itemInvoice->slack = $this->generate_slack('invoice_items');
                            $itemInvoice->course_id = $data['course_id'];
                            $itemInvoice->invoice_id = $invoice->id;
                            $itemInvoice->quantity = $data['quantity'];
                            // invoice_items tiene subtotal/total, no 'amount' -- esa
                            // columna no existe: guardar ahí tiraba un QueryException
                            // real ("Unknown column 'amount'") en cada ejecución con
                            // ítems, sin ninguna alerta salvo el log del scheduler.
                            $itemInvoice->subtotal = $data['total_amount'];
                            $itemInvoice->total = $data['total_amount'];
                            $itemInvoice->created_at = now();
                            $itemInvoice->updated_at = now();
                            $itemInvoice->save();
                        }
                    }

                    event(new InvoiceCreated($invoice));

                    $this->info("Factura generada para distribuidor #{$distributor->id}: {$invoice->slack} (total: {$total}).");
                });
            } catch (\Throwable $e) {
                // Un distribuidor con datos raros no debe abortar la facturación
                // del resto: se registra y se sigue con el siguiente.
                Log::error('invoices:generate falló para un distribuidor', [
                    'distributor_id' => $distributor->id,
                    'error' => $e->getMessage(),
                ]);
                $this->error("Distribuidor #{$distributor->id}: {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }

    public function generate_slack($table)
    {
        do {
            $slack = Str::random(6);
            $exist = DB::table($table)->where('slack', $slack)->exists();
        } while ($exist);

        return $slack;
    }

    public function generate_number($table)
    {
        $lastId = DB::table($table)->max('id');

        return $lastId ? $lastId + 1 : 1;

    }
}
