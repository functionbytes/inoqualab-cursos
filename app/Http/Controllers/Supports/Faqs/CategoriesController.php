<?php

namespace App\Http\Controllers\Supports\Faqs;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Faqs\StoreFaqCategoryRequest;
use App\Models\Faq\FaqCategorie;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoriesController extends Controller
{
    public function index(Request $request)
    {

        $searchKey = $request->search;
        $available = $request->available;

        $categories = FaqCategorie::descending();

        if ($searchKey) {
            $categories = $categories->where('title', 'like', '%'.$searchKey.'%');
        }

        if ($request->available != null) {
            $categories = $categories->where('available', $available);
        }

        $categories = $categories->paginate(paginationNumber());

        return view('supports.views.faqs.categories.index')->with([
            'categories' => $categories,
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

        return view('supports.views.faqs.categories.create')->with([
            'availables' => $availables,
        ]);

    }

    public function edit($slack)
    {

        $categorie = FaqCategorie::slack($slack);

        $availables = collect([
            ['id' => '1', 'label' => 'Publico'],
            ['id' => '0', 'label' => 'Oculto'],
        ]);

        $availables = $availables->pluck('label', 'id');

        return view('supports.views.faqs.categories.edit')->with([
            'categorie' => $categorie,
            'availables' => $availables,
        ]);

    }

    public function update(Request $request)
    {

        $categorie = FaqCategorie::slack($request->slack);
        $categorie->title = $request->title;
        $categorie->slug = Str::slug($request->title, '-');
        $categorie->available = $request->available;
        $categorie->update();

        return response()->json([
            'success' => true,
            'message' => 'Se ha atualizado correctamente.',
        ]);

    }

    public function store(StoreFaqCategoryRequest $request)
    {

        $categorie = new FaqCategorie;
        // La tabla es `faq_categories` (singular): con 'faqs_categories'
        // generate_slack consultaba una tabla inexistente y crear una categoría
        // desde soporte reventaba con "Base table or view not found".
        $categorie->slack = $this->generate_slack('faq_categories');
        $categorie->title = $request->title;
        $categorie->slug = Str::slug($request->title, '-');
        $categorie->available = $request->available;
        $categorie->save();

        return response()->json([
            'success' => true,
            'message' => 'Se ha creado correctamente.',
        ]);

    }

    public function destroy($slack)
    {

        $categorie = FaqCategorie::slack($slack);
        $categorie->delete();

        // Bug: 'support.categories.blogs' no existe como ruta -- RouteNotFoundException
        // garantizada en cada borrado exitoso.
        return redirect()->route('support.faqs.categories');

    }
}
