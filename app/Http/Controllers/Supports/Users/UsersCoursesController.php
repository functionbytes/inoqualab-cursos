<?php

namespace App\Http\Controllers\Supports\Users;

use App\Http\Controllers\Concerns\RestrictsManageableUsers;
use App\Http\Controllers\Controller;
use App\Http\Requests\Supports\Users\BulkActionUserInscriptionRequest;
use App\Models\Inscription;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UsersCoursesController extends Controller
{
    use RestrictsManageableUsers;

    public function index(Request $request, $slack)
    {

        $user = $this->guardManageableUser(User::slack($slack));
        $searchKey = $request->search;

        $inscriptions = $user->inscriptions()->with('course');

        if ($searchKey != null) {
            // 'inscriptions' no tiene columna 'title' (pertenece a 'courses'):
            // el where() plano buscaba en una columna inexistente y rompía
            // la búsqueda con un QueryException en cuanto se usaba.
            $inscriptions = $inscriptions->whereHas('course', function ($query) use ($searchKey) {
                $query->where('title', 'like', '%'.$searchKey.'%');
            });
        }

        $inscriptions = $inscriptions->paginate(paginationNumber());

        // Stats con una sola query de agregación. La relación inscriptions()
        // trae varios ->orderBy() heredados (ver User::inscriptions()) —
        // mezclar eso con funciones de agregación sin GROUP BY revienta en
        // MySQL (error 1140), por eso ->toBase()->reorder() antes de agregar.
        $agg = $user->inscriptions()->toBase()->reorder()->selectRaw(
            'COUNT(*) total,
             SUM(culminated = 1) culminated,
             SUM(culminated = 0) pending'
        )->first();

        $stats = [
            'total' => (int) $agg->total,
            'culminated' => (int) $agg->culminated,
            'pending' => (int) $agg->pending,
        ];

        $view = $request->ajax() ? 'supports.views.users.courses._table' : 'supports.views.users.courses.index';

        return view($view)->with([
            'user' => $user,
            'inscriptions' => $inscriptions,
            'searchKey' => $searchKey,
            'stats' => $stats,
        ]);

    }

    public function postpone($slack)
    {
        $inscription = Inscription::slack($slack);
        $this->guardManageableInscriptionOwner($inscription);
        $user = $inscription->user;
        $enterprise = $user->enterprise;
        $course = $inscription->course;

        return view('supports.views.users.courses.postpone')->with([
            'user' => $user,
            'course' => $course,
            'inscription' => $inscription,
            'enterprise' => $enterprise,
        ]);

    }

    public function destroy($slack)
    {
        $inscription = Inscription::slack($slack);
        $this->guardManageableInscriptionOwner($inscription);
        $inscription->delete();

        return back();
    }

    public function bulkAction(BulkActionUserInscriptionRequest $request): JsonResponse
    {
        // No se elimina en lote la inscripcion de un usuario no gestionable
        // (manager/support) aunque su id venga en el payload.
        // with('user'): el filter() de abajo lee $inscription->user->role --
        // sin esto es una query extra por inscripción del lote (N+1).
        $inscriptions = Inscription::whereIn('id', $request->ids)->with('user')->get()
            ->filter(fn (Inscription $inscription) => ! $inscription->user || in_array($inscription->user->role, $this->manageableRoles, true));
        $count = $inscriptions->count();

        match ($request->action) {
            'delete' => Inscription::whereIn('id', $inscriptions->pluck('id'))->delete(),
        };

        return response()->json(['success' => true, 'message' => $count.' inscripción(es) procesadas.']);
    }

    /**
     * Aborta si la inscripcion pertenece a un usuario con rol no gestionable
     * por soporte (manager/support): evita ver, postergar o eliminar la
     * inscripcion de una cuenta privilegiada solo con conocer su slack.
     */
    private function guardManageableInscriptionOwner($inscription): void
    {
        if (! $inscription instanceof Inscription) {
            return;
        }

        $owner = $inscription->user;

        abort_if(
            $owner && ! in_array($owner->role, $this->manageableRoles, true),
            403,
            'No tienes autorización para gestionar este recurso.'
        );
    }
}
