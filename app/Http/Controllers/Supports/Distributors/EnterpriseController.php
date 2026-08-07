<?php

namespace App\Http\Controllers\Supports\Distributors;

use App\Http\Controllers\Controller;
use App\Models\Distributor\Distributor;
use App\Models\Distributor\DistributorEnterprise;
use App\Models\Enterprise\Enterprise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EnterpriseController extends Controller
{
    public function index(Request $request, $slack)
    {

        $distributor = Distributor::slack($slack);

        $enterprises = $distributor->enterprises();

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

        return view('supports.views.distributors.enterprises.index')->with([
            'distributor' => $distributor,
            'enterprises' => $enterprises,
            'searchKey' => $searchKey,
        ]);

    }

    public function assignments($slack)
    {

        $distributor = Distributor::slack($slack);

        $enterprise = $distributor->enterprises;

        $enterprises = Enterprise::available()->get();
        $enterprises = $enterprises->pluck('title', 'id');

        return view('supports.views.distributors.enterprises.assignments')->with([
            'distributor' => $distributor,
            'enterprises' => $enterprises,
            'enterprise' => $enterprise,
        ]);

    }

    public function store(Request $request)
    {
        $distributor = Distributor::slack($request->distributor);

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
            $enterprise->available = $request->available;
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
            'message' => 'Empresa creada correctamente.',
        ]);
    }

    public function destroy($slack)
    {

        $enterprise = Enterprise::slack($slack);
        $distributor = $enterprise->distributor;
        $enterprise->delete();

        // 143 de 252 empresas no tienen distribuidor: sin él, generar la ruta
        // con slack=null lanza UrlGenerationException tras ya haber borrado.
        if (! $distributor) {
            return redirect()->back();
        }

        return redirect()->route('support.distributors.enterprises', $distributor->slack);
    }

    public function create($slack)
    {

        $distributor = Distributor::slack($slack);

        $availables = collect([
            ['id' => '1', 'label' => 'Publico'],
            ['id' => '0', 'label' => 'Oculto'],
        ]);

        $availables = $availables->pluck('label', 'id');

        return view('supports.views.distributors.enterprises.create')->with([
            'availables' => $availables,
            'distributor' => $distributor,
        ]);

    }

    public function edit($slack)
    {

        $enterprise = Enterprise::slack($slack);
        $distributor = $enterprise->distributor;

        $availables = collect([
            ['id' => '1', 'label' => 'Publico'],
            ['id' => '0', 'label' => 'Oculto'],
        ]);

        $availables = $availables->pluck('label', 'id');

        return view('supports.views.distributors.enterprises.edit')->with([
            'availables' => $availables,
            'enterprise' => $enterprise,
            'distributor' => $distributor,
        ]);

    }

    public function update(Request $request)
    {
        $enterprise = Enterprise::slack($request->slack);

        if (Enterprise::where('email', $request->email)->where('id', '!=', $enterprise->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'El correo electrónico ya está registrado en nuestro sistema.']);
        }

        if (Enterprise::where('nit', $request->nit)->where('id', '!=', $enterprise->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'El NIT ya está registrado en nuestro sistema.']);
        }

        $enterprise->title = Str::upper($request->title);
        $enterprise->slug = Str::slug($request->title, '-');
        $enterprise->address = $request->address;
        $enterprise->cellphone = $request->cellphone;
        $enterprise->nit = $request->nit;
        $enterprise->email = $request->email;
        $enterprise->available = $request->available;
        $enterprise->save();

        return response()->json([
            'success' => true,
            'slack' => $enterprise->slack,
            'message' => 'Empresa actualizada correctamente.',
        ]);
    }

    public function updateAssignments(Request $request)
    {

        $distributor = Distributor::slack($request->slack);

        if (! $distributor) {

            return response()->json([
                'success' => false,
                'message' => 'Distribuidor no encontrado.',
            ]);
        }

        $currentEnterprises = $distributor->enterprises->pluck('id')->toArray();

        $requestedEnterprises = $request->enterprises ? explode(',', $request->enterprises) : [];
        // Sin Form Request aquí: se filtra contra ids de empresa reales antes
        // de sync() (no había ninguna validación de existencia sobre estos ids).
        $newEnterprises = Enterprise::query()->whereIn('id', $requestedEnterprises)->pluck('id')->all();

        if (! empty($newEnterprises)) {

            $toDetach = array_diff($currentEnterprises, $newEnterprises);

            // sync() en una sola operación atómica dentro de una transacción:
            // el detach()+attach() en loop suelto podía dejar al distribuidor
            // con MENOS empresas que antes y ninguna nueva si un attach() a
            // mitad de camino fallaba (mismo patrón ya corregido en
            // BundlesController::update()).
            DB::transaction(function () use ($distributor, $newEnterprises) {
                $distributor->enterprises()->sync($newEnterprises);
            });

            return response()->json([
                'success' => true,
                'message' => 'Se asignaron las empresas seleccionadas.',
                'detached_enterprises' => $toDetach,
                'attached_enterprises' => array_diff($newEnterprises, $currentEnterprises),
            ]);

        }

        return response()->json([
            'success' => false,
            'message' => 'No se lograron reasignar las empresas seleccionadas.',
        ]);

    }
}
