<?php

namespace App\Http\Controllers\Managers\Enterprises;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Enterprises\StoreEnterpriseRequest;
use App\Http\Requests\Managers\Enterprises\UpdateEnterpriseRequest;
use App\Models\Enterprise\Enterprise;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EnterprisesController extends Controller
{
    public function index(Request $request)
    {

        $searchKey = $request->search;
        $available = $request->available;

        $enterprises = Enterprise::descending();

        if ($searchKey) {
            $enterprises = $enterprises->where('title', 'like', '%'.$searchKey.'%');
        }

        if ($request->available != null) {
            $enterprises = $enterprises->where('available', $available);
        }

        $enterprises = $enterprises->paginate(paginationNumber());

        $view = request()->ajax() ? 'managers.views.enterprises.enterprises._table' : 'managers.views.enterprises.enterprises.index';

        return view($view)->with([
            'enterprises' => $enterprises,
            'available' => $available,
            'searchKey' => $searchKey,
        ]);

    }

    public function create()
    {
        abort_unless(auth()->user()->can('enterprises.create'), 403);

        $availables = $this->availableOptions();

        return view('managers.views.enterprises.enterprises.create')->with([
            'availables' => $availables,
        ]);

    }

    public function edit($slack)
    {
        abort_unless(auth()->user()->can('enterprises.update'), 403);

        $enterprise = Enterprise::slack($slack);

        $availables = $this->availableOptions();

        return view('managers.views.enterprises.enterprises.edit')->with([
            'availables' => $availables,
            'enterprise' => $enterprise,
        ]);

    }

    public function update(UpdateEnterpriseRequest $request)
    {
        $data = $request->validated();
        $enterprise = Enterprise::slack($data['slack']);

        if (Enterprise::where('email', $data['email'])->where('id', '!=', $enterprise->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'El correo electrónico ya está registrado en nuestro sistema.']);
        }

        if (! empty($data['nit']) && Enterprise::where('nit', $data['nit'])->where('id', '!=', $enterprise->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'El NIT ya está registrado en nuestro sistema.']);
        }

        $enterprise->title = Str::upper($data['title']);
        $enterprise->slug = Str::slug($data['title'], '-');
        $enterprise->address = $data['address'];
        $enterprise->cellphone = $data['cellphone'] ?? null;
        $enterprise->nit = $data['nit'] ?? null;
        $enterprise->email = $data['email'];
        $enterprise->available = $data['available'];
        $enterprise->save();

        return response()->json([
            'success' => true,
            'message' => 'Empresa actualizada correctamente.',
        ]);
    }

    public function store(StoreEnterpriseRequest $request)
    {
        $data = $request->validated();

        if (Enterprise::where('email', $data['email'])->exists()) {
            return response()->json(['success' => false, 'message' => 'El correo electrónico ya está registrado en nuestro sistema.']);
        }

        if (Enterprise::where('nit', $data['nit'])->exists()) {
            return response()->json(['success' => false, 'message' => 'El NIT ya está registrado en nuestro sistema.']);
        }

        $enterprise = new Enterprise;
        $enterprise->slack = $this->generate_slack('enterprises');
        $enterprise->title = Str::upper($data['title']);
        $enterprise->slug = Str::slug($data['title'], '-');
        $enterprise->address = $data['address'];
        $enterprise->cellphone = $data['cellphone'] ?? null;
        $enterprise->nit = $data['nit'];
        $enterprise->email = $data['email'];
        $enterprise->available = 1;
        $enterprise->save();

        return response()->json([
            'success' => true,
            'message' => 'Empresa creada correctamente.',
        ]);
    }

    public function destroy($slack)
    {
        abort_unless(auth()->user()->can('enterprises.delete'), 403);

        $enterprise = Enterprise::slack($slack);
        $enterprise->delete();

        return redirect()->route('manager.enterprises');

    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => ['required', 'in:publish,hide,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:enterprises,id'],
        ]);

        $permission = $request->action === 'delete' ? 'enterprises.delete' : 'enterprises.update';
        abort_unless(auth()->user()->can($permission), 403);

        $query = Enterprise::whereIn('id', $request->ids);
        $count = $query->count();

        match ($request->action) {
            'publish' => $query->update(['available' => 1]),
            'hide' => $query->update(['available' => 0]),
            'delete' => $query->delete(),
        };

        return response()->json(['success' => true, 'message' => $count.' empresa(s) procesadas.']);
    }

    public function navegation($slack)
    {
        abort_unless(auth()->user()->can('enterprises.view'), 403);

        $enterprise = Enterprise::slack($slack);

        return view('managers.views.enterprises.enterprises.navegation')->with([
            'enterprise' => $enterprise,
        ]);

    }
}
