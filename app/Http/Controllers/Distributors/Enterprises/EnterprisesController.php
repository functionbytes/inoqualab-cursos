<?php

namespace App\Http\Controllers\Distributors\Enterprises;

use App\Http\Controllers\Controller;
use App\Http\Requests\Distributors\Enterprises\StoreEnterpriseRequest;
use App\Http\Requests\Distributors\Enterprises\UpdateEnterpriseRequest;
use App\Models\Distributor\DistributorEnterprise;
use App\Models\Enterprise\Enterprise;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EnterprisesController extends Controller
{
    public function index(Request $request)
    {

        $distributor = app('distributor');
        $enterprises = $distributor->enterprises()->descending();

        $searchKey = $request->search;
        $available = $request->available;

        if ($searchKey) {
            $enterprises = $enterprises->where(function ($query) use ($searchKey) {
                $query->where('enterprises.nit', 'like', '%'.$searchKey.'%')
                    ->orWhere('enterprises.email', 'like', '%'.$searchKey.'%')
                    ->orWhere('enterprises.title', 'like', '%'.$searchKey.'%');
            });
        }

        if ($request->available != null) {
            $enterprises = $enterprises->where('available', $available);
        }

        $enterprises = $enterprises->paginate(paginationNumber());

        return view('distributors.views.enterprises.enterprises.index')->with([
            'enterprises' => $enterprises,
            'available' => $available,
            'searchKey' => $searchKey,
        ]);
    }

    public function create()
    {

        $availables = collect([
            ['id' => '1', 'label' => 'Publico'],
            ['id' => '0', 'label' => 'Oculto'],
        ]);

        $availables = $availables->pluck('label', 'id');

        return view('distributors.views.enterprises.enterprises.create')->with([
            'availables' => $availables,
        ]);

    }

    public function edit($slack)
    {
        // Ownership: solo empresas del distribuidor autenticado (evita IDOR por slack).
        $enterprise = app('distributor')->enterprises()->where('enterprises.slack', $slack)->firstOrFail();

        $availables = collect([
            ['id' => '1', 'label' => 'Publico'],
            ['id' => '0', 'label' => 'Oculto'],
        ]);

        $availables = $availables->pluck('label', 'id');

        return view('distributors.views.enterprises.enterprises.edit')->with([
            'availables' => $availables,
            'enterprise' => $enterprise,
        ]);

    }

    public function update(UpdateEnterpriseRequest $request): JsonResponse
    {
        // El distribuidor solo puede editar empresas que le pertenecen.
        $distributor = app('distributor');
        $enterprise = $distributor->enterprises()->where('enterprises.slack', $request->slack)->first();

        if (! $enterprise) {
            return response()->json(['success' => false, 'message' => 'Empresa no encontrada o no autorizada.']);
        }

        if (Enterprise::where('email', $request->email)->where('id', '!=', $enterprise->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'El correo electrónico ya está registrado en nuestro sistema.']);
        }

        if (Enterprise::where('nit', $request->nit)->where('id', '!=', $enterprise->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'El NIT ya está registrado en nuestro sistema.']);
        }

        $enterprise = Enterprise::slack($request->slack);
        $enterprise->title = Str::upper($request->title);
        $enterprise->slug = Str::slug($request->title, '-');
        $enterprise->address = $request->address;
        $enterprise->cellphone = $request->cellphone;
        $enterprise->nit = $request->nit;
        $enterprise->email = $request->email;
        $enterprise->available = $request->available;
        $enterprise->update();

        return response()->json([
            'success' => true,
            'slack' => $enterprise->slack,
            'message' => 'Se actualizó la empresa correctamente.',
        ]);
    }

    public function store(StoreEnterpriseRequest $request): JsonResponse
    {
        $distributor = app('distributor');

        if (Enterprise::where('email', $request->email)->exists()) {
            return response()->json(['success' => false, 'message' => 'El correo electrónico ya está registrado en nuestro sistema.']);
        }

        if (Enterprise::where('nit', $request->nit)->exists()) {
            return response()->json(['success' => false, 'message' => 'El NIT ya está registrado en nuestro sistema.']);
        }

        $enterprise = DB::transaction(function () use ($request, $distributor) {
            $enterprise = new Enterprise;
            $enterprise->slack = $this->generate_slack('enterprises');
            $enterprise->title = Str::upper($request->title);
            $enterprise->slug = Str::slug($request->title, '-');
            $enterprise->address = $request->address;
            $enterprise->cellphone = $request->cellphone;
            $enterprise->nit = $request->nit;
            $enterprise->email = $request->email;
            $enterprise->available = 1;
            $enterprise->save();

            $connection = new DistributorEnterprise;
            $connection->enterprise_id = $enterprise->id;
            $connection->distributor_id = $distributor->id;
            $connection->available = 1;
            $connection->save();

            return $enterprise;
        });

        return response()->json([
            'success' => true,
            'slack' => $enterprise->slack,
            'message' => 'Se ha creado la empresa correctamente.',
        ]);
    }

    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'action' => ['required', 'in:publish,hide,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:enterprises,id'],
        ]);

        // Ownership: solo empresas que pertenecen al distribuidor autenticado (evita IDOR).
        $enterpriseIds = app('distributor')->enterprises()
            ->whereIn('enterprises.id', $request->ids)
            ->pluck('enterprises.id');

        $query = Enterprise::whereIn('id', $enterpriseIds);
        $count = $query->count();

        match ($request->action) {
            'publish' => $query->update(['available' => 1]),
            'hide' => $query->update(['available' => 0]),
            'delete' => $query->delete(),
        };

        return response()->json(['success' => true, 'message' => $count.' empresa(s) procesadas.']);
    }

    public function destroy($slack)
    {
        // Solo se puede eliminar una empresa que pertenezca al distribuidor autenticado.
        $distributor = app('distributor');
        $enterprise = $distributor->enterprises()->where('enterprises.slack', $slack)->first();

        if (! $enterprise) {
            // Bug: redirigía a manager.enterprises (dominio Managers) en vez
            // de la ruta equivalente de este portal.
            return redirect()->route('distributor.enterprises')->with('error', 'Empresa no encontrada o no autorizada.');
        }

        $enterprise->delete();

        return redirect()->route('distributor.enterprises');
    }

    public function navegation($slack)
    {
        // Ownership: la empresa debe pertenecer al distribuidor (evita IDOR por slack).
        $distributor = app('distributor');
        $enterprise = $distributor->enterprises()->where('enterprises.slack', $slack)->firstOrFail();

        return view('distributors.views.enterprises.enterprises.navegation')->with([
            'distributor' => $distributor,
            'enterprise' => $enterprise,
        ]);

    }
}
