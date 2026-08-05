<?php

namespace App\Http\Controllers\Distributors\Enterprises;

use App\Exports\Distributors\IncomesExport;
use App\Exports\Distributors\UsersExport;
use App\Http\Controllers\Controller;
use App\Models\Distributor\Distributor;
use App\Models\Distributor\DistributorEnterprise;
use App\Models\Enterprise\Enterprise;
use App\Models\Enterprise\EnterpriseUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    /** Empresa que pertenece al distribuidor autenticado, o 404 (evita IDOR). */
    private function managedEnterprise(?string $slack): Enterprise
    {
        // El slack llega de un POST sin validar: sin este guard, pasar
        // null a un parámetro `string` lanzaba TypeError y devolvía un
        // 500 en vez de un 404 limpio.
        abort_if($slack === null || $slack === '', 404);

        return app('distributor')->enterprises()->where('enterprises.slack', $slack)->firstOrFail();
    }

    /** Usuario que pertenece (enterprise_user) a una empresa del distribuidor, o 404. */
    private function managedUser(?string $slack): User
    {
        // El slack llega de un POST sin validar: sin este guard, pasar
        // null a un parámetro `string` lanzaba TypeError y devolvía un
        // 500 en vez de un 404 limpio.
        abort_if($slack === null || $slack === '', 404);

        $enterpriseIds = app('distributor')->enterprises()->pluck('enterprises.id')->all();

        return User::where('slack', $slack)
            ->whereExists(fn ($q) => $q->selectRaw('1')->from('enterprise_user')
                ->whereColumn('enterprise_user.user_id', 'users.id')
                ->whereIn('enterprise_user.enterprise_id', $enterpriseIds))
            ->firstOrFail();
    }

    public function index(Request $request, $slack)
    {

        $enterprise = $this->managedEnterprise($slack);
        $searchKey = $request->search;
        $available = $request->available;

        $users = $enterprise->users();

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

        return view('distributors.views.enterprises.users.users.index')->with([
            'users' => $users,
            'enterprise' => $enterprise,
            'available' => $available,
            'searchKey' => $searchKey,
        ]);
    }

    public function create($slack)
    {

        $enterprise = $this->managedEnterprise($slack);

        $availables = collect([
            ['id' => '1', 'label' => 'Activo'],
            ['id' => '0', 'label' => 'Inactivo'],
        ]);

        $availables = $availables->pluck('label', 'id');

        return view('distributors.views.enterprises.users.users.create')->with([
            'enterprise' => $enterprise,
            'availables' => $availables,
        ]);
    }

    public function view($slack)
    {

        $user = $this->managedUser($slack);
        $enterprise = $user->relations;

        // La empresa puede haberse borrado (soft delete) después de asociar
        // al usuario; la vista lee $enterprise->title sin null-check.
        abort_unless($enterprise instanceof Enterprise, 404, 'La empresa de este usuario ya no existe.');

        return view('distributors.views.enterprises.users.users.view')->with([
            'user' => $user,
            'enterprise' => $enterprise,
        ]);
    }

    public function edit($slack)
    {

        $user = $this->managedUser($slack);
        $enterprise = $user->relations;

        // La empresa puede haberse borrado (soft delete) después de asociar
        // al usuario; la vista lee $enterprise->id/slack sin null-check.
        abort_unless($enterprise instanceof Enterprise, 404, 'La empresa de este usuario ya no existe.');

        $availables = collect([
            ['id' => '1', 'label' => 'Activo'],
            ['id' => '0', 'label' => 'Inactivo'],
        ]);

        $availables = $availables->pluck('label', 'id');

        return view('distributors.views.enterprises.users.users.edit')->with([
            'user' => $user,
            'enterprise' => $enterprise,
            'availables' => $availables,
        ]);

    }

    public function update(Request $request): JsonResponse
    {
        $user = $this->managedUser($request->slack);

        if (User::where('email', $request->email)->where('id', '!=', $user->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'El correo electrónico ya está registrado en nuestro sistema.']);
        }

        if (User::where('identification', $request->identification)->where('id', '!=', $user->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'La identificación ya está registrada en nuestro sistema.']);
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

        $user->update();

        return response()->json(['success' => true, 'message' => 'Se ha actualizado correctamente el perfil.']);
    }

    public function store(Request $request): JsonResponse
    {
        $enterprise = $this->managedEnterprise($request->enterprise);

        if (User::where('email', $request->email)->exists()) {
            return response()->json(['success' => false, 'message' => 'El correo electrónico ya está registrado en nuestro sistema.']);
        }

        if (User::where('identification', $request->identification)->exists()) {
            return response()->json(['success' => false, 'message' => 'La identificación ya está registrada en nuestro sistema.']);
        }

        $user = new User;
        $user->slack = $this->generate_slack('users');
        $user->firstname = Str::upper($request->firstname);
        $user->lastname = Str::upper($request->lastname);
        $user->cellphone = $request->cellphone;
        $user->identification = $request->identification;
        $user->email = $request->email;
        $user->address = $request->address;
        $user->password = $request->filled('password') ? $request->password : $request->identification;
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

        return response()->json(['success' => true, 'message' => '']);
    }

    public function courses($slack)
    {

        $user = $this->managedUser($slack);
        $inscriptions = $user->inscriptions()->with('course');
        $inscriptions = $inscriptions->paginate(paginationNumber());

        return view('distributors.views.enterprises.users.courses.index')->with([
            'user' => $user,
            'inscriptions' => $inscriptions,
        ]);
    }

    public function report($slack)
    {

        $enterprise = $this->managedEnterprise($slack);

        $modalities = collect([
            ['id' => '0', 'title' => 'Todos'],
            ['id' => '1', 'title' => 'Publico'],
            ['id' => '2', 'title' => 'Inactivos'],
        ]);

        $modalities = $modalities->pluck('title', 'id');

        return view('distributors.views.enterprises.users.users.report')->with([
            'modalities' => $modalities,
            'enterprise' => $enterprise,
        ]);
    }

    public function income($slack)
    {

        $enterprise = $this->managedEnterprise($slack);

        $courses = $enterprise->courses;
        $courses = $courses->pluck('title', 'id');
        $courses->prepend('Todos', '0');

        return view('distributors.views.enterprises.users.users.income')->with([
            'courses' => $courses,
            'enterprise' => $enterprise,
        ]);
    }

    public function generate(Request $request)
    {

        // Ownership: la empresa debe pertenecer al distribuidor autenticado
        // (evita descargar el reporte de usuarios de una empresa ajena).
        // UsersExport necesita el modelo (llama $this->enterprise->users()), no un id crudo.
        $modalitie = $request->modalitie;
        $enterprise = app('distributor')->enterprises()->where('enterprises.id', $request->enterprise)->firstOrFail();

        return Excel::download(new UsersExport($enterprise, $modalitie), 'REPORTE USUARIOS '.date('Y-m-d').'.xlsx');
    }

    public function incoming(Request $request)
    {

        // Ownership: la empresa debe pertenecer al distribuidor autenticado
        // (evita descargar el reporte de ingresos de una empresa ajena).
        $enterprise = app('distributor')->enterprises()->where('enterprises.id', $request->enterprise)->firstOrFail();

        $course = $request->course;
        $range = parse_date_range($request->range);

        if ($range === null) {
            return back()->with('error', 'Selecciona un rango de fechas válido para generar el reporte.');
        }

        [$start, $end] = $range;

        return Excel::download(new IncomesExport($enterprise->id, $course, $start, $end), 'REPORTE USUARIOS '.date('Y-m-d').'.xlsx');
    }

    public function check(Request $request)
    {
        $user = User::where('identification', $request->identification)->first();

        if ($user) {
            $enterpriseUser = EnterpriseUser::where('user_id', $user->id)->first();

            if ($enterpriseUser) {
                $enterprise = Enterprise::find($enterpriseUser->enterprise_id);

                // Ownership: si la empresa del usuario NO pertenece al distribuidor
                // autenticado, no revelar el nombre de la empresa ni del distribuidor
                // ajeno (evita mapear la cartera de clientes de un competidor).
                $managed = $enterprise && app('distributor')->enterprises()->where('enterprises.id', $enterprise->id)->exists();

                if (! $managed) {
                    $response = [
                        'success' => true,
                        'message' => 'Este usuario ya está registrado en otra empresa.',
                        'enterprise' => null,
                    ];
                } else {
                    $distributorEnterprise = DistributorEnterprise::where('enterprise_id', $enterprise->id)->first();

                    if ($distributorEnterprise) {

                        $distributor = Distributor::find($distributorEnterprise->distributor_id);

                        $response = [
                            'success' => true,
                            'message' => 'Este usuario ya está registrado y asignado a la empresa: '.$enterprise->title,
                            'enterprise' => $enterprise->title,
                            'distributor' => $distributor?->title, // Información del distribuidor
                            'url' => route('distributor.enterprises.users', ['slack' => $enterprise->slack]),
                        ];

                    } else {
                        $response = [
                            'success' => true,
                            'message' => 'Este usuario ya está registrado en la empresa: '.$enterprise->title.', pero no está asignado a ningún distribuidor.',
                            'enterprise' => $enterprise->title,
                            'distributor' => null,
                        ];
                    }
                }
            } else {
                $response = [
                    'success' => true,
                    'message' => 'Este usuario ya está registrado pero no está asignado a ninguna empresa.',
                    'enterprise' => null,
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Este usuario no está registrado.',
            ];
        }

        return response()->json($response);
    }
}
