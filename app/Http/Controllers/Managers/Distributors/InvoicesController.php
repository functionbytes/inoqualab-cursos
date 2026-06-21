<?php

namespace App\Http\Controllers\Managers\Distributors;

use App\Http\Controllers\Controller;
use App\Models\Distributor\Distributor;
use App\Models\Invoice\InvoiceCondition;
use App\Models\Invoice\InvoiceMethod;
use Illuminate\Http\Request;

class InvoicesController extends Controller
{
    public function index(Request $request, $slack)
    {

        $searchKey = $request->search;
        $condition = $request->condition;
        $type = $request->type;
        $method = $request->methods;

        $invoices = Distributor::slack($slack)->invoices()->with(['condition', 'method'])->orderBy('number', 'desc');
        $methods = InvoiceMethod::latest()->get();
        $conditions = InvoiceCondition::latest()->get();

        if ($searchKey) {
            $invoices = $invoices->where('reference', 'like', '%'.$searchKey.'%');
        }

        if ($method) {
            $invoices = $invoices->where('method_id', $method);
        }

        if ($condition) {
            $invoices = $invoices->where('condition_id', $condition);
        }

        $invoices = $invoices->paginate(paginationNumber());

        return view('managers.views.invoices.invoices.index')->with([
            'invoices' => $invoices,
            'conditions' => $conditions,
            'condition' => $condition,
            'type' => $type,
            'methods' => $methods,
            'method' => $method,
            'searchKey' => $searchKey,
        ]);

    }
}
