<?php

namespace App\Http\Controllers\Supports\Enterprises;

use App\Http\Controllers\Controller;
use App\Models\Enterprise\Enterprise;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EnterprisesController extends Controller
{
    public function index(Request $request)
    {

        $enterprises = Enterprise::descending();

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

        // 1 query de agregación en vez de 3 counts sueltos.
        $agg = Enterprise::query()->selectRaw(
            'COUNT(*) total,
             SUM(available = 1) `public`,
             SUM(available = 0) hidden'
        )->first();

        $stats = [
            'total' => (int) $agg->total,
            'public' => (int) $agg->public,
            'hidden' => (int) $agg->hidden,
        ];

        return view('supports.views.enterprises.enterprises.enterprises.index')->with([
            'enterprises' => $enterprises,
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

        return view('supports.views.enterprises.enterprises.enterprises.create')->with([
            'availables' => $availables,
        ]);

    }

    public function edit($slack)
    {

        $enterprise = Enterprise::slack($slack);

        $availables = collect([
            ['id' => '1', 'label' => 'Publico'],
            ['id' => '0', 'label' => 'Oculto'],
        ]);

        $availables = $availables->pluck('label', 'id');

        return view('supports.views.enterprises.enterprises.enterprises.edit')->with([
            'availables' => $availables,
            'enterprise' => $enterprise,
        ]);

    }

    public function update(Request $request)
    {
        $enterprise = Enterprise::slack($request->slack);

        if ($enterprise->email != $request->email) {
            $emailExists = Enterprise::query()
                ->where('email', $request->email)
                ->where('id', '!=', $enterprise->id)
                ->exists();

            if ($emailExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'El correo electronico ya estan regitrada en nuestro sistema',
                ]);
            }
        }

        if ($enterprise->nit != $request->nit) {
            $nitExists = Enterprise::query()
                ->where('nit', $request->nit)
                ->where('id', '!=', $enterprise->id)
                ->exists();

            if ($nitExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'El nit ya estan regitrada en nuestro sistema',
                ]);
            }
        }

        $enterprise->title = Str::upper($request->title);
        $enterprise->slug = Str::slug($request->title, '-');
        $enterprise->address = $request->address;
        $enterprise->cellphone = $request->cellphone;
        $enterprise->nit = $request->nit;
        $enterprise->email = $request->email;
        $enterprise->code = $request->filled('code') ? Str::upper(trim($request->code)) : null;
        $enterprise->available = $request->available;
        $enterprise->save();

        return response()->json([
            'success' => true,
            'slack' => $enterprise->slack,
            'message' => 'Se actualizo la empresa correctamente',
        ]);
    }

    public function store(Request $request)
    {
        if (Enterprise::query()->where('email', $request->email)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'El correo electronico ya estan regitrada en nuestro sistema',
            ]);
        }

        if (Enterprise::query()->where('nit', $request->nit)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'El nit ya estan regitrada en nuestro sistema',
            ]);
        }

        $enterprise = new Enterprise;
        $enterprise->slack = $this->generate_slack('enterprises');
        $enterprise->title = Str::upper($request->title);
        $enterprise->slug = Str::slug($request->title, '-');
        $enterprise->address = $request->address;
        $enterprise->cellphone = $request->cellphone;
        $enterprise->nit = $request->nit;
        $enterprise->email = $request->email;
        $enterprise->code = $request->filled('code') ? Str::upper(trim($request->code)) : null;
        $enterprise->available = 1;
        $enterprise->save();

        return response()->json([
            'success' => true,
            'slack' => $enterprise->slack,
            'message' => 'Se creado la empresa correctamente',
        ]);
    }

    public function destroy($slack)
    {

        $enterprise = Enterprise::slack($slack);
        $enterprise->delete();

        return redirect()->route('support.enterprises');
    }

    public function navegation($slack)
    {

        $enterprise = Enterprise::slack($slack);

        return view('supports.views.enterprises.enterprises.enterprises.navegation')->with([
            'enterprise' => $enterprise,
        ]);

    }

    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'action' => ['required', 'in:delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:enterprises,id'],
        ]);

        $query = Enterprise::whereIn('id', $request->ids);
        $count = $query->count();

        match ($request->action) {
            'delete' => $query->delete(),
        };

        return response()->json(['success' => true, 'message' => $count.' empresa(s) procesadas.']);
    }
}
