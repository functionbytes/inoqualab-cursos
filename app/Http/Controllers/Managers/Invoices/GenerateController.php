<?php

namespace App\Http\Controllers\Managers\Invoices;

use App\Http\Controllers\Controller;
use App\Models\Invoice\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

class GenerateController extends Controller
{
    public function generate($slack)
    {
        abort_unless(auth()->user()->can('invoices.view'), 403);

        $invoice = Invoice::slack($slack);

        if (! $invoice) {
            abort(404);
        }

        $pdf = Pdf::loadView('managers.views.invoices.invoices.print', [
            'invoice' => $invoice,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('factura-'.$invoice->number.'.pdf');
    }
}
