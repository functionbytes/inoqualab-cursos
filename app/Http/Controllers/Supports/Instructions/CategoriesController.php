<?php

namespace App\Http\Controllers\Supports\Instructions;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Instructions\StoreInstructionCategoryRequest;
use App\Models\Instruction\InstructionCategorie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoriesController extends Controller
{
    public function index(Request $request): View
    {

        $searchKey = $request->search;
        $available = $request->available;

        $categories = InstructionCategorie::descending();

        if ($searchKey) {
            $categories = $categories->where('title', 'like', '%'.$searchKey.'%');
        }

        if ($request->available != null) {
            $categories = $categories->where('available', $available);
        }

        $categories = $categories->paginate(paginationNumber());

        // Total + desglose por estado en 1 sola query de agregación.
        $agg = InstructionCategorie::query()->selectRaw(
            'COUNT(*) total,
             SUM(available = 1) `public`,
             SUM(available = 0) hidden'
        )->first();

        $stats = [
            'total' => (int) $agg->total,
            'public' => (int) $agg->public,
            'hidden' => (int) $agg->hidden,
        ];

        return view('supports.views.instructions.categories.index')->with([
            'categories' => $categories,
            'available' => $available,
            'searchKey' => $searchKey,
            'stats' => $stats,
        ]);

    }

    public function create(): View
    {

        $availables = collect([
            ['id' => '1', 'label' => 'Publico'],
            ['id' => '0', 'label' => 'Oculto'],
        ]);

        $availables = $availables->pluck('label', 'id');

        return view('supports.views.instructions.categories.create')->with([
            'availables' => $availables,
        ]);

    }

    public function edit($slack): View
    {

        $categorie = InstructionCategorie::slack($slack);

        $availables = collect([
            ['id' => '1', 'label' => 'Publico'],
            ['id' => '0', 'label' => 'Oculto'],
        ]);

        $availables = $availables->pluck('label', 'id');

        return view('supports.views.instructions.categories.edit')->with([
            'categorie' => $categorie,
            'availables' => $availables,
        ]);

    }

    public function update(Request $request): JsonResponse
    {

        $categorie = InstructionCategorie::slack($request->slack);
        $categorie->title = $request->title;
        $categorie->icon = $request->icon;
        $categorie->slug = Str::slug($request->title, '-');
        $categorie->available = $request->available;
        $categorie->update();

        return response()->json([
            'success' => true,
            'slack' => $categorie->slack,
            'message' => 'Se actualizado la categoria correctamente',
        ]);

    }

    public function store(StoreInstructionCategoryRequest $request): JsonResponse
    {

        $categorie = new InstructionCategorie;
        $categorie->slack = $this->generate_slack('instruction_categories');
        $categorie->title = $request->title;
        $categorie->icon = $request->icon;
        $categorie->slug = Str::slug($request->title, '-');
        $categorie->available = $request->available;
        $categorie->save();

        return response()->json([
            'success' => true,
            'slack' => $categorie->slack,
            'message' => 'Se creado la categoria correctamente',
        ]);

    }

    public function destroy($slack): RedirectResponse
    {

        $categorie = InstructionCategorie::slack($slack);
        $categorie->delete();

        return redirect()->back();

    }

    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'action' => ['required', 'in:delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:instruction_categories,id'],
        ]);

        $query = InstructionCategorie::whereIn('id', $request->ids);
        $count = $query->count();

        match ($request->action) {
            'delete' => $query->delete(),
        };

        return response()->json(['success' => true, 'message' => $count.' categoría(s) procesadas.']);
    }
}
