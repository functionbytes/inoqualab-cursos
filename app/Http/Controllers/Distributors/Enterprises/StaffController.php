<?php

namespace App\Http\Controllers\Distributors\Enterprises;

use App\Http\Controllers\Concerns\ValidatesUserPassword;
use App\Http\Controllers\Controller;
use App\Http\Requests\Distributors\Enterprises\BulkActionEnterpriseStaffRequest;
use App\Models\Enterprise\Enterprise;
use App\Models\Enterprise\EnterpriseStaff;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StaffController extends Controller
{
    use ValidatesUserPassword;

    /** Empresa que pertenece al distribuidor autenticado, o 404 (evita IDOR). */
    private function managedEnterprise(?string $slack): Enterprise
    {
        // El slack llega de un POST sin validar: sin este guard, pasar
        // null a un parámetro `string` lanzaba TypeError y devolvía un
        // 500 en vez de un 404 limpio.
        abort_if($slack === null || $slack === '', 404);

        return app('distributor')->enterprises()->where('enterprises.slack', $slack)->firstOrFail();
    }

    /** Usuario que es staff de una empresa del distribuidor autenticado, o 404. */
    private function managedStaff(?string $slack): User
    {
        // El slack llega de un POST sin validar: sin este guard, pasar
        // null a un parámetro `string` lanzaba TypeError y devolvía un
        // 500 en vez de un 404 limpio.
        abort_if($slack === null || $slack === '', 404);

        $enterpriseIds = app('distributor')->enterprises()->pluck('enterprises.id')->all();

        return User::where('slack', $slack)
            ->whereExists(fn ($q) => $q->selectRaw('1')->from('enterprise_staff')
                ->whereColumn('enterprise_staff.user_id', 'users.id')
                ->whereIn('enterprise_staff.enterprise_id', $enterpriseIds))
            ->firstOrFail();
    }

    public function index(Request $request, $slack)
    {
        // Ownership: la empresa debe pertenecer al distribuidor (evita IDOR por slack).
        $enterprise = $this->managedEnterprise($slack);
        $searchKey = $request->search;
        $available = $request->available;

        $users = $enterprise->staffs()->descending();

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

        return view('distributors.views.enterprises.staffs.index')->with([
            'users' => $users,
            'enterprise' => $enterprise,
            'available' => $available,
            'searchKey' => $searchKey,
        ]);

    }

    public function create($slack)
    {
        // Ownership: la empresa debe pertenecer al distribuidor (evita IDOR por slack).
        $enterprise = $this->managedEnterprise($slack);

        $availables = collect([
            ['id' => '1', 'label' => 'Activo'],
            ['id' => '0', 'label' => 'Inactivo'],
        ]);

        $availables = $availables->pluck('label', 'id');

        return view('distributors.views.enterprises.staffs.create')->with([
            'enterprise' => $enterprise,
            'availables' => $availables,
        ]);
    }

    public function edit($slack)
    {
        // Ownership: solo staff de empresas del distribuidor (evita IDOR por slack).
        $user = $this->managedStaff($slack);

        $enterprise = $user->relationsenterprise;

        $availables = collect([
            ['id' => '1', 'label' => 'Activo'],
            ['id' => '0', 'label' => 'Inactivo'],
        ]);

        $availables = $availables->pluck('label', 'id');

        return view('distributors.views.enterprises.staffs.edit')->with([
            'user' => $user,
            'enterprise' => $enterprise,
            'availables' => $availables,
        ]);

    }

    public function update(Request $request): JsonResponse
    {
        // Ownership: solo staff de empresas del distribuidor (evita IDOR por slack).
        $user = $this->managedStaff($request->slack);

        if (User::where('email', $request->email)->where('id', '!=', $user->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'El correo electrónico ya está registrado en nuestro sistema.']);
        }

        if (User::where('identification', $request->identification)->where('id', '!=', $user->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'La identificación ya está registrada en nuestro sistema.']);
        }

        if ($error = $this->passwordValidationError($request->password)) {
            return response()->json(['success' => false, 'message' => $error]);
        }

        $user->firstname = Str::upper($request->firstname);
        $user->lastname = Str::upper($request->lastname);
        $user->cellphone = $request->cellphone;
        $user->email = $request->email;
        $user->address = $request->address;
        $user->company = $request->company;
        $user->available = $request->available;
        $user->identification = $request->identification;

        if ($request->filled('password')) {
            $user->password = $request->password;
        }

        $user->update();

        return response()->json(['success' => true, 'message' => 'Se ha actualizado correctamente el empleado.']);
    }

    public function store(Request $request): JsonResponse
    {
        // Ownership: la empresa debe pertenecer al distribuidor (evita IDOR por slack).
        $enterprise = $this->managedEnterprise($request->enterprise);

        if (User::where('email', $request->email)->exists()) {
            return response()->json(['success' => false, 'message' => 'El correo electrónico ya está registrado en nuestro sistema.']);
        }

        if (User::where('identification', $request->identification)->exists()) {
            return response()->json(['success' => false, 'message' => 'La identificación ya está registrada en nuestro sistema.']);
        }

        DB::transaction(function () use ($request, $enterprise) {
            $user = new User;
            $user->slack = $this->generate_slack('users');
            $user->firstname = Str::upper($request->firstname);
            $user->lastname = Str::upper($request->lastname);
            $user->cellphone = $request->cellphone;
            $user->identification = $request->identification;
            $user->email = $request->email;
            // Sin password explícita, un valor aleatorio (no la identificación,
            // un dato semi-público) — el cliente la establece vía "olvidé mi contraseña".
            $user->password = $request->filled('password') ? $request->password : Str::random(16);
            $user->available = 1;
            $user->role = 'enterprise';
            $user->terms = 1;
            $user->page = 0;
            $user->setting = 0;
            $user->validation = 1;
            $user->email_verified_at = Carbon::now()->setTimezone('America/Bogota');
            $user->save();

            $inscription = new EnterpriseStaff;
            $inscription->user_id = $user->id;
            $inscription->enterprise_id = $enterprise->id;
            $inscription->available = 1;
            $inscription->created_at = Carbon::now()->setTimezone('America/Bogota');
            $inscription->updated_at = Carbon::now()->setTimezone('America/Bogota');
            $inscription->save();
        });

        return response()->json(['success' => true, 'message' => 'Se ha creado correctamente el empleado.']);
    }

    public function destroy($slack)
    {
        // Ownership: solo staff de empresas del distribuidor (evita borrar usuarios ajenos).
        $user = $this->managedStaff($slack);
        $user->delete();

        return redirect()->back();

    }

    public function bulkAction(BulkActionEnterpriseStaffRequest $request, $slack): JsonResponse
    {
        // Ownership: la empresa debe pertenecer al distribuidor (evita IDOR por slack).
        $enterprise = $this->managedEnterprise($slack);

        $memberIds = $enterprise->staffs()
            ->whereIn('users.id', $request->ids)
            ->pluck('users.id');

        $query = User::whereIn('id', $memberIds);
        $count = $query->count();

        match ($request->action) {
            'activate' => $query->update(['available' => 1]),
            'deactivate' => $query->update(['available' => 0]),
            'delete' => $query->delete(),
        };

        return response()->json(['success' => true, 'message' => $count.' empleado(s) procesados.']);
    }
}
