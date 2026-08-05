<?php

namespace App\Http\Controllers\Accountings\Invoices;

use App\Http\Controllers\Controller;
use App\Models\Distributor\Distributor;
use App\Models\Invoice\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

class GenerateController extends Controller
{
    public function generate($slack)
    {
        $invoice = Invoice::slack($slack);

        if (! $invoice) {
            abort(404);
        }

        // El distribuidor puede haberse borrado (soft delete) después de
        // emitir la factura; la vista lee $invoice->distributor->title sin
        // null-check.
        abort_unless($invoice->distributor instanceof Distributor, 404, 'El distribuidor de esta factura ya no existe.');

        $pdf = Pdf::loadView('accountings.views.invoices.invoices.print', [
            'invoice' => $invoice,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('factura-'.$invoice->number.'.pdf');
    }
}
