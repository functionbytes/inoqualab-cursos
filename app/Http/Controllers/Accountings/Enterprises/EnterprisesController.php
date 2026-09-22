<?php

namespace App\Http\Controllers\Accountings\Enterprises;

use App\Http\Controllers\Controller;
use App\Models\Enterprise\Enterprise;

class EnterprisesController extends Controller
{
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
