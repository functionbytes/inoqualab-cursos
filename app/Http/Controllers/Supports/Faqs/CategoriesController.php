<?php

namespace App\Http\Controllers\Supports\Faqs;

use App\Http\Controllers\Controller;
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

    public function store(Request $request)
    {

        $categorie = new FaqCategorie;
        $categorie->slack = $this->generate_slack('faqs_categories');
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

        return redirect()->route('support.categories.blogs');

    }
}
