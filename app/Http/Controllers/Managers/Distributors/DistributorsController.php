<?php

namespace App\Http\Controllers\Managers\Distributors;

use App\Http\Controllers\Controller;
use App\Models\Distributor\Distributor;
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
            $distributors = $distributors->where('title', 'like', '%'.$searchKey.'%');
        }

        if ($request->available != null) {
            $distributors = $distributors->where('available', $available);
        }

        $distributors = $distributors->paginate(paginationNumber());

        return view('managers.views.distributors.distributors.index')->with([
            'distributors' => $distributors,
            'available' => $available,
            'searchKey' => $searchKey,
        ]);

    }

    public function create()
    {

        $availables = $this->availableOptions();

        $generates = collect([
            ['id' => '1', 'label' => 'Si'],
            ['id' => '0', 'label' => 'No'],
        ]);

        $generates = $generates->pluck('label', 'id');

        return view('managers.views.distributors.distributors.create')->with([
            'availables' => $availables,
            'generates' => $generates,
        ]);

    }

    public function edit($slack)
    {

        $distributor = Distributor::slack($slack);

        $availables = $this->availableOptions();

        $generates = collect([
            ['id' => '1', 'label' => 'Si'],
            ['id' => '0', 'label' => 'No'],
        ]);

        $generates = $generates->pluck('label', 'id');

        return view('managers.views.distributors.distributors.edit')->with([
            'availables' => $availables,
            'generates' => $generates,
            'distributor' => $distributor,
        ]);

    }

    public function update(Request $request)
    {
        abort_unless(auth()->user()->can('distributors.update'), 403);
        $distributor = Distributor::slack($request->slack);

        if ($distributor->email != $request->email || $distributor->nit != $request->nit) {

            $existingDistributor = Distributor::where('email', $request->email)
                ->orWhere('nit', $request->nit)
                ->first();

            if ($existingDistributor) {
                if ($existingDistributor->email == $request->email && $distributor->email != $request->email) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El correo electronico ya está registrado en nuestro sistema.',
                    ]);
                }

                // Check for nit conflict
                if ($existingDistributor->nit == $request->nit && $distributor->nit != $request->nit) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El NIT ya está registrado en nuestro sistema.',
                    ]);
                }
            }
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
        abort_unless(auth()->user()->can('distributors.create'), 403);
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
        abort_unless(auth()->user()->can('distributors.delete'), 403);

        $distributor = Distributor::slack($slack);
        $distributor->delete();

        return redirect()->route('manager.distributors');
    }

    public function navegation($slack)
    {

        $distributor = Distributor::slack($slack);

        return view('managers.views.distributors.distributors.navegation')->with([
            'distributor' => $distributor,
        ]);

    }
}
