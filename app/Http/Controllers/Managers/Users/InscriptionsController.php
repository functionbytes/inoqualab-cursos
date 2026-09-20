<?php

namespace App\Http\Controllers\Managers\Users;

use App\Http\Controllers\Controller;
use App\Models\Inscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InscriptionsController extends Controller
{
    /**
     * Mismo guard que Managers\Users\UsersController::guardNotSuperadmin():
     * un manager no-superadmin no puede ver/editar las inscripciones de una
     * cuenta superadmin.
     */
    private function guardNotSuperadmin(User $user): void
    {
        abort_if(
            $user->role === 'superadmin' && auth()->user()->role !== 'superadmin',
            403,
            'No tienes autorización para gestionar esta cuenta.'
        );
    }

    public function index(Request $request, $slack)
    {

        $user = User::slack($slack);
        $this->guardNotSuperadmin($user);

        $allInscriptions = Inscription::query()
            ->with('course')
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(paginationNumber());

        return view('managers.views.users.users.inscriptions.index')->with([
            'inscriptions' => $allInscriptions,
        ]);

    }

    public function edit($slack)
    {
        $inscription = Inscription::slack($slack);
        $user = $inscription->user;

        // El cliente puede haberse borrado (soft delete) después de crear la
        // inscripción; sin este guard, ->enterprise sobre null tira un 500.
        // Mismo bug ya arreglado en Supports\Users\InscriptionsController::edit().
        abort_unless($user instanceof User, 404, 'El cliente de esta inscripción ya no existe.');
        $this->guardNotSuperadmin($user);

        $course = $inscription->course;

        return view('managers.views.users.users.inscriptions.edit')->with([
            'user' => $user,
            'course' => $course,
            'inscription' => $inscription,
        ]);

    }

    public function action(Request $request)
    {
        // Aquí NO sirve parse_date_range(): este picker emite DD/MM/YYYY y el
        // helper usa Carbon::parse, que leería 05/03/2026 como mayo en vez de
        // marzo. Se mantiene createFromFormat y solo se añade el guard.
        $date_var = explode(' - ', $request->range);

        if (count($date_var) !== 2) {
            return response()->json([
                'success' => false,
                'message' => 'Selecciona un rango de fechas válido.',
            ], 422);
        }

        try {
            $start = Carbon::createFromFormat('d/m/Y', trim($date_var[0]))->format('Y-m-d');
            $expire = Carbon::createFromFormat('d/m/Y', trim($date_var[1]))->format('Y-m-d');
        } catch (\Throwable) {
            return response()->json([
                'success' => false,
                'message' => 'El rango de fechas no es válido.',
            ], 422);
        }

        $inscription = Inscription::slack($request->inscription);

        if ($inscription instanceof Inscription && $inscription->user) {
            $this->guardNotSuperadmin($inscription->user);
        }

        $inscription->enroll_start = $start;
        $inscription->enroll_expire = $expire;
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
