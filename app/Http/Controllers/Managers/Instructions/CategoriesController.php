<?php

namespace App\Http\Controllers\Managers\Instructions;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Instructions\StoreInstructionCategoryRequest;
use App\Models\Instruction\InstructionCategorie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoriesController extends Controller
{
    public function index(Request $request)
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

        return view('managers.views.settings.instructions.categories.index')->with([
            'categories' => $categories,
            'available' => $available,
            'searchKey' => $searchKey,
        ]);

    }

    public function create()
    {

        $availables = $this->availableOptions();

        return view('managers.views.settings.instructions.categories.create')->with([
            'availables' => $availables,
        ]);

    }

    public function edit($slack)
    {

        $categorie = InstructionCategorie::slack($slack);

        $availables = $this->availableOptions();

        return view('managers.views.settings.instructions.categories.edit')->with([
            'categorie' => $categorie,
            'availables' => $availables,
        ]);

    }

    public function update(Request $request)
    {
        abort_unless(auth()->user()->can('instructions.update'), 403);

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

    public function store(StoreInstructionCategoryRequest $request)
    {
        abort_unless(auth()->user()->can('instructions.create'), 403);

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

    public function destroy($slack)
    {
        abort_unless(auth()->user()->can('instructions.delete'), 403);

        $categorie = InstructionCategorie::slack($slack);
        $categorie->delete();

        return redirect()->back();

    }

    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'action' => ['required', 'in:publish,hide,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:instruction_categories,id'],
        ]);

        $permission = $request->action === 'delete' ? 'instructions.delete' : 'instructions.update';
        abort_unless(auth()->user()->can($permission), 403);

        $query = InstructionCategorie::whereIn('id', $request->ids);
        $count = $query->count();

        match ($request->action) {
            'publish' => $query->update(['available' => 1]),
            'hide' => $query->update(['available' => 0]),
            'delete' => $query->delete(),
        };

        return response()->json(['success' => true, 'message' => $count.' categoría(s) procesadas.']);
    }
}
