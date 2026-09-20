<?php

namespace App\Http\Controllers\Supports\Distributors;

use App\Http\Controllers\Controller;
use App\Models\Distributor\Distributor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DistributorsController extends Controller
{
    public function index(Request $request)
    {

        $searchKey = $request->search;
        $available = $request->available;

        $distributors = Distributor::descending();

        if ($searchKey) {
            $distributors = $distributors->where(function ($query) use ($searchKey) {
                $query->where('distributors.nit', 'like', '%'.$searchKey.'%')
                    ->orWhere('distributors.email', 'like', '%'.$searchKey.'%')
                    ->orWhere('distributors.title', 'like', '%'.$searchKey.'%');
            });
        }

        if ($request->available != null) {
            $distributors = $distributors->where('available', $available);
        }

        $distributors = $distributors->paginate(paginationNumber());

        // 4 counts en 1 query con agregación condicional, igual que
        // Managers\Courses\CoursesController::index().
        $agg = Distributor::query()->selectRaw(
            'COUNT(*) total,
             SUM(available = 1) `public`,
             SUM(available = 0) hidden,
             SUM(enterprise_generate = 1) enterprise_generate'
        )->first();

        $stats = [
            'total' => (int) $agg->total,
            'public' => (int) $agg->public,
            'hidden' => (int) $agg->hidden,
            'enterprise_generate' => (int) $agg->enterprise_generate,
        ];

        return view('supports.views.distributors.distributors.index')->with([
            'distributors' => $distributors,
            'available' => $available,
            'searchKey' => $searchKey,
            'stats' => $stats,
        ]);

    }

    public function create()
    {

        $availables = collect([
            ['id' => '1', 'label' => 'Publico'],
            ['id' => '0', 'label' => 'Oculto'],
        ]);

        $availables = $availables->pluck('label', 'id');

        $generates = collect([
            ['id' => '1', 'label' => 'Si'],
            ['id' => '0', 'label' => 'No'],
        ]);

        $generates = $generates->pluck('label', 'id');

        return view('supports.views.distributors.distributors.create')->with([
            'availables' => $availables,
            'generates' => $generates,
        ]);

    }

    public function edit($slack)
    {

        $distributor = Distributor::slack($slack);

        $availables = collect([
            ['id' => '1', 'label' => 'Publico'],
            ['id' => '0', 'label' => 'Oculto'],
        ]);

        $availables = $availables->pluck('label', 'id');

        $generates = collect([
            ['id' => '1', 'label' => 'Si'],
            ['id' => '0', 'label' => 'No'],
        ]);

        $generates = $generates->pluck('label', 'id');

        return view('supports.views.distributors.distributors.edit')->with([
            'availables' => $availables,
            'generates' => $generates,
            'distributor' => $distributor,
        ]);

    }

    public function view($slack)
    {

        $distributor = Distributor::slack($slack);

        return view('supports.views.distributors.distributors.view')->with([
            'distributor' => $distributor,
        ]);

    }

    public function update(Request $request)
    {
        // Sin esta validacion, un campo faltante (address/cellphone/nit/email/
        // leading/supporting/available son NOT NULL en BD) lanza un 500 crudo
        // de MySQL en vez de un 422 con mensaje claro.
        $request->validate([
            'title' => ['required', 'string', 'min:3', 'max:100'],
            'address' => ['required', 'string', 'min:3', 'max:100'],
            'cellphone' => ['required', 'string', 'min:6', 'max:10'],
            'nit' => ['required', 'string', 'min:6', 'max:100'],
            'email' => ['required', 'email'],
            'leading' => ['required', 'string', 'min:3', 'max:100'],
            'supporting' => ['required', 'string', 'min:3', 'max:100'],
            'available' => ['required', 'in:0,1'],
            'enterprise_generate' => ['nullable', 'in:0,1'],
        ]);

        $distributor = Distributor::slack($request->slack);

        if (Distributor::where('email', $request->email)->where('id', '!=', $distributor->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'El correo electrónico ya está registrado en nuestro sistema.']);
        }

        if (Distributor::where('nit', $request->nit)->where('id', '!=', $distributor->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'El NIT ya está registrado en nuestro sistema.']);
        }

        $distributor->title = Str::upper($request->title);
        $distributor->slug = Str::slug($request->title, '-');
        $distributor->address = $request->address;
        $distributor->cellphone = $request->cellphone;
        $distributor->enterprise_generate = $request->enterprise_generate;
        $distributor->nit = $request->nit;
        $distributor->leading = $request->leading;
        $distributor->supporting = $request->supporting;
        $distributor->email = $request->email;
        $distributor->available = $request->available;
        $distributor->save();

        return response()->json([
            'success' => true,
            'message' => 'Distribuidor actualizado correctamente.',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string', 'min:3', 'max:100'],
            'address' => ['required', 'string', 'min:3', 'max:100'],
            'cellphone' => ['required', 'string', 'min:6', 'max:10'],
            'nit' => ['required', 'string', 'min:6', 'max:100'],
            'email' => ['required', 'email'],
            'leading' => ['required', 'string', 'min:3', 'max:100'],
            'supporting' => ['required', 'string', 'min:3', 'max:100'],
            'enterprise_generate' => ['nullable', 'in:0,1'],
        ]);

        if (Distributor::where('email', $request->email)->exists()) {
            return response()->json(['success' => false, 'message' => 'El correo electrónico ya está registrado en nuestro sistema.']);
        }

        if (Distributor::where('nit', $request->nit)->exists()) {
            return response()->json(['success' => false, 'message' => 'El NIT ya está registrado en nuestro sistema.']);
        }

        $distributor = new Distributor;
        $distributor->slack = $this->generate_slack('distributors');
        $distributor->title = Str::upper($request->title);
        $distributor->slug = Str::slug($request->title, '-');
        $distributor->address = $request->address;
        $distributor->cellphone = $request->cellphone;
        $distributor->enterprise_generate = $request->enterprise_generate;
        $distributor->nit = $request->nit;
        $distributor->email = $request->email;
        $distributor->available = 1;
        $distributor->leading = $request->leading;
        $distributor->supporting = $request->supporting;
        $distributor->save();

        return response()->json([
            'success' => true,
            'message' => 'Distribuidor creado correctamente.',
        ]);
    }

    public function destroy($slack)
    {

        $distributor = Distributor::slack($slack);
        $distributor->delete();

        return redirect()->route('support.distributors');
    }

    public function navegation($slack)
    {

        $distributor = Distributor::slack($slack);

        return view('supports.views.distributors.distributors.navegation')->with([
            'distributor' => $distributor,
        ]);

    }

    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'action' => ['required', 'in:delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:distributors,id'],
        ]);

        $query = Distributor::whereIn('id', $request->ids);
        $count = $query->count();

        match ($request->action) {
            'delete' => $query->delete(),
        };

        return response()->json(['success' => true, 'message' => $count.' distribuidor(es) procesados.']);
    }
}
