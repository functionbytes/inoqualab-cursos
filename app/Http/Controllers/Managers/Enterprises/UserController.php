<?php

namespace App\Http\Controllers\Managers\Enterprises;

use App\Exports\Managers\IncomesExport;
use App\Exports\Managers\UsersExport;
use App\Http\Controllers\Controller;
use App\Imports\Managers\UsersImport;
use App\Models\Enterprise\Enterprise;
use App\Models\Enterprise\EnterpriseUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class UserController extends Controller
{
    public function index(Request $request, $slack)
    {

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

        return view('managers.views.enterprises.users.index')->with([
            'users' => $users,
            'enterprise' => $enterprise,
            'available' => $available,
            'searchKey' => $searchKey,
        ]);

    }

    public function create($slack)
    {

        $enterprise = Enterprise::slack($slack);

        $availables = collect([
            ['id' => '1', 'label' => 'Activo'],
            ['id' => '0', 'label' => 'Inactivo'],
        ]);

        $availables = $availables->pluck('label', 'id');

        return view('managers.views.enterprises.users.create')->with([
            'enterprise' => $enterprise,
            'availables' => $availables,
        ]);

    }

    public function edit($slack)
    {

        $user = User::slack($slack);

        $enterprise = $user->relations;

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
        $user = User::slack($request->slack);

        abort_unless($user instanceof User, 404);

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

            if ($request->role == 'enterprises') {
                $user->enterprise_id = $request->enterprise;
            } elseif ($user->role == 'customers') {
                $enterprise = $user->relation;

                if ($enterprise != null) {
                    $enterprise->enterprise_id = $request->enterprises;
                    $enterprise->save();
                } else {
                    $inscription = new EnterpriseUser;
                    $inscription->user_id = $user->id;
                    $inscription->enterprise_id = $request->enterprises;
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

    public function report($slack)
    {

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

        $enterprise = Enterprise::slack($slack);

        $courses = $enterprise->courses;
        $courses = $courses->pluck('title', 'id');
        $courses->prepend('Todos', '0');

        return view('managers.views.enterprises.users.income')->with([
            'courses' => $courses,
            'enterprise' => $enterprise,
        ]);

    }

    public function import($slack)
    {

        $enterprise = Enterprise::slack($slack);

        return view('managers.views.enterprises.users.import')->with([
            'enterprise' => $enterprise,
        ]);

    }

    public function importation(Request $request)
    {

        $enterprise = Enterprise::slack($request->enterprise);

        if ($request->hasFile('file') && $request->file('file')->isValid()) {

            try {
                Excel::import(new UsersImport($enterprise->slack), request()->file('file'));
            } catch (ValidationException $e) {

                $failures = $e->failures();

                return view('managers.views.enterprises.users.response')->with([
                    'error_message' => $e->getMessage(),
                    'failures' => $failures,
                    'enterprise' => $enterprise,
                ]);
            }
        }

        return redirect()->route('manager.enterprises.users', $enterprise->slack);
    }

    public function generate(Request $request)
    {

        $modalitie = $request->modalitie;
        $enterprise = $request->enterprise;

        return Excel::download(new UsersExport($enterprise, $modalitie), 'REPORTE USUARIOS '.date('Y-m-d').'.xlsx');
    }

    public function incoming(Request $request)
    {

        $course = $request->course;
        $enterprise = $request->enterprise;
        $date = explode(' - ', $request->range);
        $start = Carbon::parse($date[0])->startOfDay();
        $end = Carbon::parse($date[1])->endOfDay();

        return Excel::download(new IncomesExport($enterprise, $course, $start, $end), 'REPORTE USUARIOS '.date('Y-m-d').'.xlsx');

    }
}
