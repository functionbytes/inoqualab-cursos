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

            $items = $distributor->orders()->date($startDate, $endDate)->get();

            if ($items->count() > 0) {

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

                foreach ($items as $item) {

                    $order = $item->order;

                    if ($order) {

                        $total += $order->total_order_amount;

                        foreach ($order->items as $orderItem) {

                            $itemType = $orderItem->item_type;
                            $itemId = $orderItem->item_id;
                            $itemPrice = $orderItem->amount;

                            if (! isset($coursesItems[$itemType])) {
                                $coursesItems[$itemType] = [];
                            }

                            if (! isset($coursesItems[$itemType][$itemId])) {
                                $coursesItems[$itemType][$itemId] = [
                                    'quantity' => 0,
                                    'total_amount' => 0,
                                    'course_id' => 0,
                                ];
                            }

                            $coursesItems[$itemType][$itemId]['quantity'] += $orderItem->quantity;
                            $coursesItems[$itemType][$itemId]['total_amount'] += $itemPrice;
                            $coursesItems[$itemType][$itemId]['course_id'] = $itemId;

                        }
                    }
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
                        $itemInvoice->amount = $data['total_amount'];
                        $itemInvoice->created_at = now();
                        $itemInvoice->updated_at = now();
                        $itemInvoice->save();
                    }
                }

                event(new InvoiceCreated($invoice));

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
