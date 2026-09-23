<?php

namespace App\Http\Controllers\Managers\Faqs;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Faqs\BulkActionFaqCategoryRequest;
use App\Http\Requests\Managers\Faqs\StoreFaqCategoryRequest;
use App\Http\Requests\Managers\Faqs\UpdateFaqCategoryRequest;
use App\Models\Faq\FaqCategorie;
use Illuminate\Http\JsonResponse;
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

        $view = request()->ajax() ? 'managers.views.settings.faqs.categories._table' : 'managers.views.settings.faqs.categories.index';

        return view($view)->with([
            'categories' => $categories,
            'available' => $available,
            'searchKey' => $searchKey,
        ]);

    }

    public function create()
    {

        $availables = $this->availableOptions();

        return view('managers.views.settings.faqs.categories.create')->with([
            'availables' => $availables,
        ]);

    }

    public function edit($slack)
    {

        $categorie = FaqCategorie::slack($slack);

        $availables = $this->availableOptions();

        return view('managers.views.settings.faqs.categories.edit')->with([
            'categorie' => $categorie,
            'availables' => $availables,
        ]);

    }

    public function store(StoreFaqCategoryRequest $request)
    {
        abort_unless(auth()->user()->can('faqs.create'), 403);

        $categorie = new FaqCategorie;
        $categorie->slack = $this->generate_slack('faq_categories');
        $categorie->title = $request->title;
        $categorie->slug = Str::slug($request->title, '-');
        $categorie->available = $request->available;
        $categorie->save();

        return response()->json([
            'success' => true,
            'message' => 'Se ha creado correctamente',
        ]);

    }

    public function update(UpdateFaqCategoryRequest $request)
    {
        $categorie = FaqCategorie::slack($request->slack);
        $categorie->title = $request->title;
        $categorie->slug = Str::slug($request->title, '-');
        $categorie->available = $request->available;
        $categorie->update();

        return response()->json([
            'success' => true,
            'message' => 'Se ha actualizo correctamente',
        ]);

    }

    public function destroy($slack)
    {
        abort_unless(auth()->user()->can('faqs.delete'), 403);

        $categorie = FaqCategorie::slack($slack);
        $categorie->delete();

        return redirect()->back();

    }

    public function bulkAction(BulkActionFaqCategoryRequest $request): JsonResponse
    {
        $query = FaqCategorie::whereIn('id', $request->ids);
        $count = $query->count();

        match ($request->action) {
            'publish' => $query->update(['available' => 1]),
            'hide' => $query->update(['available' => 0]),
            'delete' => $query->delete(),
        };

        return response()->json(['success' => true, 'message' => $count.' categoría(s) procesadas.']);
    }
}
