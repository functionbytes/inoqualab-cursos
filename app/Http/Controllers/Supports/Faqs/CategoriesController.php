<?php

namespace App\Http\Controllers\Supports\Faqs;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Faqs\StoreFaqCategoryRequest;
use App\Http\Requests\Supports\BulkActionFaqCategoryRequest;
use App\Http\Requests\Supports\UpdateFaqCategoryRequest;
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

        // Total + desglose por estado en 1 sola query de agregación.
        $agg = FaqCategorie::query()->selectRaw(
            'COUNT(*) total,
             SUM(available = 1) `public`,
             SUM(available = 0) hidden'
        )->first();

        $stats = [
            'total' => (int) $agg->total,
            'public' => (int) $agg->public,
            'hidden' => (int) $agg->hidden,
        ];

        $view = $request->ajax() ? 'supports.views.faqs.categories._table' : 'supports.views.faqs.categories.index';

        return view($view)->with([
            'categories' => $categories,
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

    public function update(UpdateFaqCategoryRequest $request)
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

    public function bulkAction(BulkActionFaqCategoryRequest $request): JsonResponse
    {
        $query = FaqCategorie::whereIn('id', $request->ids);
        $count = $query->count();

        match ($request->action) {
            'delete' => $query->delete(),
        };

        return response()->json(['success' => true, 'message' => $count.' categoría(s) procesadas.']);
    }
}
