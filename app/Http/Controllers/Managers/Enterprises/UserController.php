<?php

namespace App\Http\Controllers\Managers\Enterprises;

use App\Exports\Managers\IncomesExport;
use App\Exports\Managers\UsersExport;
use App\Http\Controllers\Concerns\RestrictsManageableUsers;
use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Enterprises\ImportUsersRequest;
use App\Imports\Managers\UsersImport;
use App\Models\Enterprise\Enterprise;
use App\Models\Enterprise\EnterpriseUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class UserController extends Controller
{
    use RestrictsManageableUsers;

    public function index(Request $request, $slack)
    {
        abort_unless(auth()->user()->can('enterprises.view'), 403);

        $enterprise = Enterprise::slack($slack);
        $searchKey = $request->search;
        $available = $request->available;

        $users = $enterprise->users()->orderBy('users.updated_at', 'desc');

        if ($searchKey) {
            $users = $users->where('users.firstname', 'like', '%'.$searchKey.'%')->orWhere('users.lastname', 'like', '%'.$searchKey.'%')->orWhere('users.email', $searchKey)->orWhere('users.identification', $searchKey);
        }

        if ($available != null) {
            $users = $users->where('users.available', $available);
        }

        $users = $users->paginate(paginationNumber());

        $view = request()->ajax() ? 'managers.views.enterprises.users._table' : 'managers.views.enterprises.users.index';

        return view($view)->with([
            'users' => $users,
            'enterprise' => $enterprise,
            'available' => $available,
            'searchKey' => $searchKey,
        ]);

    }

    public function create($slack)
    {
        abort_unless(auth()->user()->can('enterprises.create'), 403);

        $enterprise = Enterprise::slack($slack);

        return view('managers.views.enterprises.users.create')->with([
            'enterprise' => $enterprise,
        ]);

    }

    public function edit($slack)
    {
        abort_unless(auth()->user()->can('enterprises.update'), 403);

        $user = User::slack($slack);

        // Sin empresa asociada (usuario huérfano o empresa borrada) esta ficha
        // no tiene sentido: la vista pinta $enterprise->slack y reventaba con
        // "Attempt to read property on null".
        $enterprise = $user->relations;
        abort_if($enterprise === null, 404);

        $availables = collect([
            ['id' => '1', 'label' => 'Activo'],
            ['id' => '0', 'label' => 'Inactivo'],
        ]);

        $availables = $availables->pluck('label', 'id');

        return view('managers.views.enterprises.users.edit')->with([
            'user' => $user,
            'enterprise' => $enterprise,
            'availables' => $availables,
        ]);

    }

    public function update(Request $request)
    {
        abort_unless(auth()->user()->can('enterprises.update'), 403);

        // Sin este guard, un manager con solo el permiso enterprises.update
        // podia cambiar password/email de CUALQUIER usuario del sistema
        // (incluidos otros manager/support) enviando su slack aqui.
        $user = $this->guardManageableUser(User::slack($request->slack));

        if ($user->email !== $request->email) {
            $emailExists = User::where('email', $request->email)->where('id', '!=', $user->id)->exists();
            if ($emailExists) {
                return response()->json(['success' => false, 'message' => 'El correo electronico ya estan regitrada en nuestro sistema']);
            }
        }

        if ($request->identification && $user->identification !== $request->identification) {
            $identificationExists = User::where('identification', $request->identification)->where('id', '!=', $user->id)->exists();
            if ($identificationExists) {
                return response()->json(['success' => false, 'message' => 'El nit ya estan regitrada en nuestro sistema']);
            }
        }

        DB::transaction(function () use ($request, $user) {
            $user->firstname = Str::upper($request->firstname);
            $user->lastname = Str::upper($request->lastname);
            $user->cellphone = $request->cellphone;
            $user->email = $request->email;
            $user->address = $request->address;
            $user->company = $request->company;
            $user->available = $request->available;

            if ($request->filled('password')) {
                $user->password = $request->password;
            }

            if ($request->role === 'enterprise') {
                $user->enterprise_id = $request->enterprise;
            } elseif ($user->role === 'customer') {
                $enterprise = $user->relation;

                if ($enterprise != null) {
                    $enterprise->enterprise_id = $request->enterprise;
                    $enterprise->save();
                } else {
                    $inscription = new EnterpriseUser;
                    $inscription->user_id = $user->id;
                    $inscription->enterprise_id = $request->enterprise;
                    $inscription->available = 1;
                    $inscription->created_at = Carbon::now()->setTimezone('America/Bogota');
                    $inscription->updated_at = Carbon::now()->setTimezone('America/Bogota');
                    $inscription->save();
                }
            }

            $user->update();
        });

        return response()->json([
            'success' => true,
            'message' => 'El actualizado correctamente',
        ]);
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('enterprises.create'), 403);

        $enterprise = Enterprise::slack($request->enterprise);

        $emailExists = User::where('email', $request->email)->exists();
        if ($emailExists) {
            return response()->json(['success' => false, 'message' => 'El correo electronico ya estan regitrada en nuestro sistema']);
        }

        if ($request->identification) {
            $identificationExists = User::where('identification', $request->identification)->exists();
            if ($identificationExists) {
                return response()->json(['success' => false, 'message' => 'El nit ya estan regitrada en nuestro sistema']);
            }
        }

        DB::transaction(function () use ($request, $enterprise) {
            $user = new User;
            $user->slack = $this->generate_slack('users');
            $user->firstname = Str::upper($request->firstname);
            $user->lastname = Str::upper($request->lastname);
            $user->cellphone = $request->cellphone;
            $user->identification = $request->identification;
            $user->email = $request->email;
            $user->address = $request->address;
            $user->password = $request->password;
            $user->available = 1;
            $user->role = 'customer';
            $user->terms = 1;
            $user->page = 0;
            $user->setting = 0;
            $user->validation = 1;
            $user->email_verified_at = Carbon::now()->setTimezone('America/Bogota');
            $user->save();

            $inscription = new EnterpriseUser;
            $inscription->user_id = $user->id;
            $inscription->enterprise_id = $enterprise->id;
            $inscription->available = 1;
            $inscription->created_at = Carbon::now()->setTimezone('America/Bogota');
            $inscription->updated_at = Carbon::now()->setTimezone('America/Bogota');
            $inscription->save();
        });

        return response()->json([
            'success' => true,
            'message' => 'Se ha crado correctamente',
        ]);
    }

    public function bulkAction(Request $request, $slack): JsonResponse
    {
        abort_unless(auth()->user()->can('enterprises.update'), 403);

        $request->validate([
            'action' => ['required', 'in:activate,deactivate'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:users,id'],
        ]);

        $enterprise = Enterprise::slack($slack);

        // Acotado a los ids que realmente pertenecen a esta empresa (mismo
        // criterio que actionReasign()): sin esto se podría activar/desactivar
        // un usuario ajeno a la empresa (IDOR).
        $memberIds = $enterprise->users()->whereIn('users.id', $request->ids)->pluck('users.id');

        $query = User::whereIn('id', $memberIds);
        $count = $query->count();

        match ($request->action) {
            'activate' => $query->update(['available' => 1]),
            'deactivate' => $query->update(['available' => 0]),
        };

        return response()->json(['success' => true, 'message' => $count.' usuario(s) procesados.']);
    }

    public function report($slack)
    {
        abort_unless(auth()->user()->can('enterprises.view'), 403);

        $enterprise = Enterprise::slack($slack);

        $modalities = collect([
            ['id' => '0', 'title' => 'Todos'],
            ['id' => '1', 'title' => 'Publico'],
            ['id' => '2', 'title' => 'Inactivos'],
        ]);

        $modalities = $modalities->pluck('title', 'id');

        return view('managers.views.enterprises.users.report')->with([
            'modalities' => $modalities,
            'enterprise' => $enterprise,
        ]);
    }

    public function income($slack)
    {
        abort_unless(auth()->user()->can('enterprises.view'), 403);

        $enterprise = Enterprise::slack($slack);

        $courses = $enterprise->courses;
        $courses = $courses->pluck('title', 'id');
        $courses->prepend('Todos', '0');

        return view('managers.views.enterprises.users.income')->with([
            'courses' => $courses,
            'enterprise' => $enterprise,
        ]);

    }

    public function importation(ImportUsersRequest $request)
    {
        abort_unless(auth()->user()->can('enterprises.update'), 403);

        $enterprise = Enterprise::slack($request->validated('enterprise'));

        try {
            Excel::import(new UsersImport($enterprise->slack), $request->file('file'));
        } catch (ValidationException $e) {

            $failures = $e->failures();

            return view('managers.views.enterprises.users.response')->with([
                'error_message' => $e->getMessage(),
                'failures' => $failures,
                'enterprise' => $enterprise,
            ]);
        }

        return redirect()->route('manager.enterprises.users', $enterprise->slack);
    }

    public function generate(Request $request)
    {
        abort_unless(auth()->user()->can('enterprises.view'), 403);

        // UsersExport necesita el modelo (llama $this->enterprise->users()), no un id crudo.
        $modalitie = $request->modalitie;
        $enterprise = Enterprise::id($request->enterprise);

        return Excel::download(new UsersExport($enterprise, $modalitie), 'REPORTE USUARIOS '.date('Y-m-d').'.xlsx');
    }

    public function incoming(Request $request)
    {
        abort_unless(auth()->user()->can('enterprises.view'), 403);

        $course = $request->course;
        // IncomesExport espera el id crudo (lo usa en un where con DB::table),
        // pero Enterprise::id() valida su existencia y aborta 404 si no existe.
        $enterprise = Enterprise::id($request->enterprise)->id;
        $date = explode(' - ', $request->range);

        abort_unless(count($date) === 2, 422, 'Rango de fechas invalido.');

        $start = Carbon::parse($date[0])->startOfDay();
        $end = Carbon::parse($date[1])->endOfDay();

        return Excel::download(new IncomesExport($enterprise, $course, $start, $end), 'REPORTE USUARIOS '.date('Y-m-d').'.xlsx');

    }
}
