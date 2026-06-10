<?php

namespace App\Http\Controllers\Accountings\Distributors;

use App\Http\Controllers\Controller;
use App\Models\Distributor\Distributor;
use Illuminate\Http\Request;

class DistributorsController extends Controller
{
    public function index(Request $request)
    {

        $searchKey = $request->search;
        $available = $request->available;

        $distributors = Distributor::descending();

        if ($searchKey) {
            $distributors = $distributors->where('title', 'like', '%'.$searchKey.'%');
        }

        if ($request->available != null) {
            $distributors = $distributors->where('available', $available);
        }

        $distributors = $distributors->paginate(paginationNumber());

        return view('accountings.views.distributors.distributors.index')->with([
            'distributors' => $distributors,
            'available' => $available,
            'searchKey' => $searchKey,
        ]);

    }

    public function view($slack)
    {

        $distributor = Distributor::slack($slack);

        $availables = collect([
            ['id' => '1', 'label' => 'Publico'],
            ['id' => '0', 'label' => 'Oculto'],
        ]);

        $availables = $availables->pluck('label', 'id');

        return view('accountings.views.distributors.distributors.view')->with([
            'availables' => $availables,
            'distributor' => $distributor,
        ]);

    }
}
