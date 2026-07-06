<?php

namespace App\Http\Controllers\Supports\Enterprises;

use App\Exports\Supports\IncomesExport;
use App\Exports\Supports\UsersExport;
use App\Http\Controllers\Concerns\RestrictsManageableUsers;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Supports\Concerns\ValidatesUniqueUserFields;
use App\Models\Distributor\Distributor;
use App\Models\Distributor\DistributorEnterprise;
use App\Models\Enterprise\Enterprise;
use App\Models\Enterprise\EnterpriseUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    use RestrictsManageableUsers, ValidatesUniqueUserFields;

    public function index(Request $request, $slack)
    {

        $enterprise = Enterprise::slack($slack);
        $searchKey = $request->search;
        $available = $request->available;

        $users = $enterprise->users()->orderBy('users.updated_at', 'desc')
            ->with(['certificates', 'inscriptions']);

        if ($searchKey) {
            $users = $users->where(function ($query) use ($searchKey) {
                $query->where('users.firstname', 'like', '%'.$searchKey.'%')
                    ->orWhere('users.lastname', 'like', '%'.$searchKey.'%')
                    ->orWhere(DB::raw("CONCAT(users.firstname, ' ', users.lastname)"), 'like', '%'.$searchKey.'%')
                    ->orWhere('users.email', 'like', '%'.$searchKey.'%')->orWhere('users.identification', 'like', '%'.$searchKey.'%');
            });
        }

        if ($available != null) {
            $users = $users->where('users.available', $available);
        }
        $users = $users->paginate(paginationNumber());

        return view('supports.views.enterprises.users.users.index')->with([
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

        return view('supports.views.enterprises.users.users.create')->with([
            'enterprise' => $enterprise,
            'availables' => $availables,
        ]);
    }

    public function view($slack)
    {

        $user = $this->guardManageableUser(User::slack($slack));
        $enterprise = $user->relations;

        return view('supports.views.enterprises.users.users.view')->with([
            'user' => $user,
            'enterprise' => $enterprise,
        ]);
    }

    public function edit($slack)
    {

        $user = $this->guardManageableUser(User::slack($slack));
        $enterprise = $user->relations;

        $availables = collect([
            ['id' => '1', 'label' => 'Activo'],
            ['id' => '0', 'label' => 'Inactivo'],
        ]);

        $availables = $availables->pluck('label', 'id');

        return view('supports.views.enterprises.users.users.edit')->with([
            'user' => $user,
            'enterprise' => $enterprise,
            'availables' => $availables,
        ]);

    }

    public function update(Request $request)
    {
        $user = User::slack($request->slack);

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Usuario no encontrado.'], 404);
        }

        if ($error = $this->uniqueUserFieldError($request->email, $request->identification, $user)) {
            return response()->json(['success' => false, 'message' => $error]);
        }

        $user->firstname = Str::upper($request->firstname);
        $user->lastname = Str::upper($request->lastname);
        $user->identification = $request->identification;
        $user->cellphone = $request->cellphone;
        $user->email = $request->email;
        $user->address = $request->address;
        $user->available = $request->available;

        if ($request->filled('password')) {
            $user->password = $request->password;
        }

        $user->save();

        return response()->json(['success' => true, 'message' => 'Se ha actualizado correctamente el perfil']);
    }

    public function store(Request $request)
    {
        $enterprise = Enterprise::slack($request->enterprise);

        if ($error = $this->uniqueUserFieldError($request->email, $request->identification)) {
            return response()->json(['success' => false, 'message' => $error]);
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
            // Sin password explícita, un valor aleatorio (no la identificación,
            // un dato semi-público) — el cliente la establece vía "olvidé mi contraseña".
            $user->password = $request->filled('password') ? $request->password : Str::random(16);
            $user->role = 'customer';
            $user->available = 1;
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

        return response()->json(['success' => true, 'message' => 'Se ha creado correctamente']);
    }

    public function users($slack)
    {

        $enterprise = Enterprise::slack($slack);
        $users = $enterprise->users;

        return view('supports.views.enterprises.users.users.index')->with([
            'enterprise' => $enterprise,
            'users' => $users,
        ]);
    }

    public function courses($slack)
    {

        $user = User::slack($slack);
        $inscriptions = $user->inscriptions()->with('course');
        $inscriptions = $inscriptions->paginate(paginationNumber());

        return view('supports.views.enterprises.users.courses.index')->with([
            'user' => $user,
            'inscriptions' => $inscriptions,
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

        return view('supports.views.enterprises.users.users.report')->with([
            'modalities' => $modalities,
            'enterprise' => $enterprise,
        ]);
    }

    public function income($slack)
    {

        $enterprise = Enterprise::slack($slack);

        $courses = $enterprise->courses;
        $courses = $courses->pluck('title', 'id');

        return view('supports.views.enterprises.users.users.income')->with([
            'courses' => $courses,
            'enterprise' => $enterprise,
        ]);
    }

    public function generate(Request $request)
    {

        // UsersExport necesita el modelo (llama $this->enterprise->users()), no un id crudo.
        $modalitie = $request->modalitie;
        $enterprise = Enterprise::where('id', $request->enterprise)->firstOrFail();

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

    public function check(Request $request)
    {
        $user = User::where('identification', $request->identification)->first();

        if ($user) {
            $enterpriseUser = EnterpriseUser::where('user_id', $user->id)->first();

            if ($enterpriseUser) {
                $enterprise = Enterprise::find($enterpriseUser->enterprise_id);

                if ($enterprise) {
                    $distributorEnterprise = DistributorEnterprise::where('enterprise_id', $enterprise->id)->first();

                    if ($distributorEnterprise) {

                        $distributor = Distributor::find($distributorEnterprise->distributor_id);

                        return response()->json([
                            'success' => true,
                            'message' => 'Este usuario ya está registrado y asignado a la empresa: '.$enterprise->title,
                            'enterprise' => $enterprise->title,
                            'distributor' => $distributor->title,
                            'url' => route('distributor.supports.users', ['slack' => $enterprise->slack]),
                        ]);

                    } else {

                        return response()->json([
                            'success' => true,
                            'message' => 'Este usuario ya está registrado en la empresa: '.$enterprise->title.', pero no está asignado a ningún distribuidor.',
                            'enterprise' => $enterprise->title,
                            'distributor' => null,
                        ]);

                    }
                }
            } else {

                return response()->json(['success' => true,
                    'message' => 'Este usuario ya está registrado pero no está asignado a ninguna empresa.',
                    'enterprise' => null,
                ]);
            }
        } else {

            return response()->json([
                'success' => false,
                'message' => 'Este usuario no está registrado.',
            ]);

        }

    }
}
