<?php

namespace App\Http\Controllers\Accountings\Enterprises;

use App\Http\Controllers\Controller;
use App\Models\Enterprise\Enterprise;
use Illuminate\Http\Request;

class EnterprisesController extends Controller
{
    public function index(Request $request)
    {

        $searchKey = $request->search;
        $available = $request->available;

        $enterprises = Enterprise::descending();

        if ($searchKey) {
            $enterprises = $enterprises->where('title', 'like', '%'.$searchKey.'%');
        }

        if ($request->available != null) {
            $enterprises = $enterprises->where('available', $available);
        }

        $enterprises = $enterprises->paginate(paginationNumber());

        return view('accountings.views.enterprises.enterprises.index')->with([
            'enterprises' => $enterprises,
            'available' => $available,
            'searchKey' => $searchKey,
        ]);

    }

    public function view($slack)
    {

        $enterprise = Enterprise::slack($slack);

        $availables = collect([
            ['id' => '1', 'label' => 'Publico'],
            ['id' => '0', 'label' => 'Oculto'],
        ]);

        $availables = $availables->pluck('label', 'id');

        return view('accountings.views.enterprises.enterprises.view')->with([
            'availables' => $availables,
            'enterprise' => $enterprise,
        ]);

    }
}
