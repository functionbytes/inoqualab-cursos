<?php

namespace App\Http\Controllers\Enterprises\Users;

use App\Exports\Enterprises\UsersExport;
use App\Http\Controllers\Controller;
use App\Models\Enterprise\Enterprise;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UsersController extends Controller
{
    public function index(Request $request): View
    {

        $searchKey = $request->search ?? null;
        $enterprise = app('enterprise');
        $users = $enterprise->users()->latest();

        if (strpos($searchKey, '-')) {
            $startDate = Carbon::createFromFormat('Y-m-d', str_replace('"', '', $searchKey))->startOfDay();
            $endDate = Carbon::createFromFormat('Y-m-d', str_replace('"', '', $searchKey))->endOfDay();
            $users->whereBetween('users.updated_at', [$startDate, $endDate]);
        }

        $users->when(! strpos($searchKey, '-'), function ($query) use ($searchKey) {
            $query->where('users.firstname', 'like', '%'.$searchKey.'%')
                ->orWhere('users.lastname', 'like', '%'.$searchKey.'%')
                ->orWhere(DB::raw("CONCAT(users.firstname, ' ', users.lastname)"), 'like', '%'.$searchKey.'%')
                ->orWhere('users.email', 'like', '%'.$searchKey.'%')
                ->orWhere('users.identification', 'like', '%'.$searchKey.'%');
        });

        $users = $users->paginate(paginationNumber());

        return view('enterprises.views.users.users.index')->with([
            'users' => $users,
            'searchKey' => $searchKey,
        ]);

    }

    public function edit($slack): View
    {
        // Ownership: solo usuarios de la empresa autenticada (evita IDOR por slack).
        $user = app('enterprise')->users()->where('users.slack', $slack)->firstOrFail();

        $roles = collect([
            ['id' => 'admin', 'title' => 'Administrador'],
            ['id' => 'customers', 'title' => 'Cliente'],
            ['id' => 'enterprises', 'title' => 'Empresa'],
        ]);

        $roles = $roles->pluck('title', 'id');

        $availables = collect([
            ['id' => '1', 'label' => 'Activo'],
            ['id' => '0', 'label' => 'Inactivo'],
        ]);

        $availables = $availables->pluck('label', 'id');

        $enterprises = Enterprise::get();
        $enterprises->prepend('', '');
        $enterprises = $enterprises->pluck('title', 'id');

        $enterprise = $user->relations?->id;

        return view('enterprises.views.users.users.edit')->with([
            'user' => $user,
            'enterprises' => $enterprises,
            'enterprise' => $enterprise,
            'availables' => $availables,
            'roles' => $roles,
        ]);

    }

    public function update(Request $request): JsonResponse
    {
        // Ownership: solo usuarios de la empresa autenticada (evita IDOR por slack).
        $user = app('enterprise')->users()->where('users.slack', $request->slack)->firstOrFail();
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->identification = $request->identification;
        $user->cellphone = $request->cellphone;
        $user->email = $request->email;
        $user->address = $request->address;
        $user->company = $request->company;
        $user->available = $request->available;
        $request->password != null ? $user->password = $request->password : null;
        $user->update();

        return response()->json([
            'success' => true,
            'message' => 'Se ha actualizado correctamente',
        ]);

    }

    public function report($slack): View
    {

        $enterprise = app('enterprise');

        $modalities = collect([
            ['id' => '0', 'title' => 'Todos'],
            ['id' => '1', 'title' => 'Publico'],
            ['id' => '2', 'title' => 'Inactivos'],
        ]);

        $modalities = $modalities->pluck('title', 'id');

        return view('enterprises.views.users.users.report')->with([
            'modalities' => $modalities,
            'enterprises' => $enterprise,
            'enterprises' => $enterprise,
        ]);

    }

    public function generate(Request $request): BinaryFileResponse
    {

        $enterprise = app('enterprise');
        $modalitie = $request->modalitie;

        return Excel::download(new UsersExport($enterprise, $modalitie), 'Enterprise.xlsx');

    }
}
