<?php

namespace App\Http\Controllers\Accountings\Distributors;

use App\Http\Controllers\Controller;
use App\Models\Distributor\Distributor;
use Illuminate\Http\Request;

class EnterprisesController extends Controller
{
    public function index(Request $request, $slack)
    {

        $searchKey = $request->search;
        $available = $request->available;

        $distributor = Distributor::slack($slack);
        $enterprises = $distributor->enterprises();

        if ($searchKey) {
            $enterprises = $enterprises->where('title', 'like', '%'.$searchKey.'%');
        }

        if ($request->available != null) {
            $enterprises = $enterprises->where('available', $available);
        }

        $enterprises = $enterprises->paginate(paginationNumber());

        return view('accountings.views.distributors.enterprises.index')->with([
            'enterprises' => $enterprises,
            'available' => $available,
            'searchKey' => $searchKey,
        ]);

    }
}
