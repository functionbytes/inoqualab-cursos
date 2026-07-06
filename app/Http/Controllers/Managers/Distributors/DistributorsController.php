<?php

namespace App\Http\Controllers\Managers\Distributors;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Distributors\StoreDistributorRequest;
use App\Http\Requests\Managers\Distributors\UpdateDistributorRequest;
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

    public function update(UpdateDistributorRequest $request)
    {
        abort_unless(auth()->user()->can('distributors.update'), 403);

        $data = $request->validated();
        $distributor = Distributor::slack($data['slack']);

        if ($distributor->email != $data['email'] || $distributor->nit != $data['nit']) {

            $existingDistributor = Distributor::where('email', $data['email'])
                ->orWhere('nit', $data['nit'])
                ->first();

            if ($existingDistributor) {
                if ($existingDistributor->email == $data['email'] && $distributor->email != $data['email']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El correo electronico ya está registrado en nuestro sistema.',
                    ]);
                }

                // Check for nit conflict
                if ($existingDistributor->nit == $data['nit'] && $distributor->nit != $data['nit']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El NIT ya está registrado en nuestro sistema.',
                    ]);
                }
            }
        }

        $distributor->title = Str::upper($data['title']);
        $distributor->slug = Str::slug($data['title'], '-');
        $distributor->address = $data['address'];
        $distributor->cellphone = $data['cellphone'];
        $distributor->enterprise_generate = $data['enterprise_generate'] ?? null;
        $distributor->nit = $data['nit'];
        $distributor->leading = $data['leading'];
        $distributor->supporting = $data['supporting'];
        $distributor->email = $data['email'];
        $distributor->available = $data['available'];
        $distributor->save();

        return response()->json([
            'success' => true,
            'message' => 'Distribuidor actualizado correctamente.',
        ]);

    }

    public function store(StoreDistributorRequest $request)
    {
        abort_unless(auth()->user()->can('distributors.create'), 403);

        $data = $request->validated();

        if (Distributor::where('email', $data['email'])->exists()) {
            return response()->json(['success' => false, 'message' => 'El correo electrónico ya está registrado en nuestro sistema.']);
        }

        if (Distributor::where('nit', $data['nit'])->exists()) {
            return response()->json(['success' => false, 'message' => 'El NIT ya está registrado en nuestro sistema.']);
        }

        $distributor = new Distributor;
        $distributor->slack = $this->generate_slack('distributors');
        $distributor->title = Str::upper($data['title']);
        $distributor->slug = Str::slug($data['title'], '-');
        $distributor->address = $data['address'];
        $distributor->cellphone = $data['cellphone'];
        $distributor->enterprise_generate = $data['enterprise_generate'] ?? null;
        $distributor->nit = $data['nit'];
        $distributor->email = $data['email'];
        $distributor->available = 1;
        $distributor->leading = $data['leading'];
        $distributor->supporting = $data['supporting'];
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
