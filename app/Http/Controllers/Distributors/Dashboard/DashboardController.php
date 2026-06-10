<?php

namespace App\Http\Controllers\Distributors\Dashboard;

use App\Events\Inscriptions\InscriptionCreated;
use App\Http\Controllers\Controller;
use App\Models\Inscription;
use App\Models\User;

class DashboardController extends Controller
{
    public function dashboard()
    {

        $user = User::auth();

        // $inscription = Inscription::slack('pS39LK');
        // InscriptionCreated::dispatch($inscription);

        // $enterprise = $user->enterprise;

        // $users = $enterprise->users()->latest()->take(10)->get();

        return view('distributors.views.dashboard.index')->with([
            // 'enterprises' => $enterprise,
            // 'users' => $users,
        ]);

    }
}
