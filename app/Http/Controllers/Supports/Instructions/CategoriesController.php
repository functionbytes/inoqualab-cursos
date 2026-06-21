<?php

namespace App\Http\Controllers\Supports\Instructions;

use App\Http\Controllers\Controller;
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

        return view('supports.views.instructions.categories.index')->with([
            'categories' => $categories,
            'available' => $available,
            'searchKey' => $searchKey,
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

    public function store(Request $request): JsonResponse
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
}
