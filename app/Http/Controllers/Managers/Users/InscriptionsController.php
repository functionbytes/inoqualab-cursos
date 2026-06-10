<?php

namespace App\Http\Controllers\Managers\Users;

use App\Http\Controllers\Controller;
use App\Models\Inscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InscriptionsController extends Controller
{
    public function index(Request $request, $slack)
    {

        $user = User::slack($slack);

        $allInscriptions = Inscription::query()
            ->with('course')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return view('managers.views.users.users.inscriptions.index')->with([
            'inscriptions' => $allInscriptions,
        ]);

    }

    public function edit($slack)
    {
        $inscription = Inscription::slack($slack);
        $user = $inscription->user;
        $enterprise = $user->enterprise;
        $course = $inscription->course;

        return view('managers.views.users.users.inscriptions.edit')->with([
            'user' => $user,
            'course' => $course,
            'inscription' => $inscription,
            'enterprise' => $enterprise,
        ]);

    }

    public function action(Request $request)
    {
        $date_var = explode(' - ', $request->range);
        $inscription = Inscription::slack($request->inscription);

        $inscription->enroll_start = Carbon::createFromFormat('d/m/Y', trim($date_var[0]))->format('Y-m-d');
        $inscription->enroll_expire = Carbon::createFromFormat('d/m/Y', trim($date_var[1]))->format('Y-m-d');
        $inscription->updated_at = Carbon::now()->setTimezone('America/Bogota');
        $inscription->culminated = 0;
        $inscription->expire = 0;
        $inscription->save();

        return response()->json([
            'success' => true,
            'slack' => $inscription->slack,
            'message' => 'Se actualizo la inscription correctamente',
        ]);
    }
}
