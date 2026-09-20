<?php

namespace App\Http\Controllers\Supports\Faqs;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Faqs\StoreFaqRequest;
use App\Models\Faq\Faq;
use App\Models\Faq\FaqCategorie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FaqsController extends Controller
{
    public function index(Request $request)
    {

        $searchKey = $request->search;
        $available = $request->available;

        // with('categorie'): la vista muestra $faq->categorie->title por fila
        // -- sin esto es una query extra por FAQ listada (N+1).
        $faqs = Faq::with('categorie')->descending();

        if ($searchKey) {
            $faqs = $faqs->where('title', 'like', '%'.$searchKey.'%');
        }

        if ($request->available != null) {
            $faqs = $faqs->where('available', $available);
        }

        $faqs = $faqs->paginate(paginationNumber());

        // Total + desglose por estado en 1 sola query de agregación.
        $agg = Faq::query()->selectRaw(
            'COUNT(*) total,
             SUM(available = 1) `public`,
             SUM(available = 0) hidden'
        )->first();

        $stats = [
            'total' => (int) $agg->total,
            'public' => (int) $agg->public,
            'hidden' => (int) $agg->hidden,
        ];

        return view('supports.views.faqs.faqs.index')->with([
            'faqs' => $faqs,
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

        $categories = FaqCategorie::latest()->available()->get();
        $categories->prepend('', '');
        $categories = $categories->pluck('title', 'id');

        return view('supports.views.faqs.faqs.create')->with([
            'availables' => $availables,
            'categories' => $categories,
        ]);

    }

    public function edit($slack)
    {

        $faq = Faq::slack($slack);

        $availables = collect([
            ['id' => '1', 'label' => 'Publico'],
            ['id' => '0', 'label' => 'Oculto'],
        ]);

        $availables = $availables->pluck('label', 'id');

        $categories = FaqCategorie::latest()->available()->get();
        $categories = $categories->pluck('title', 'id');

        return view('supports.views.faqs.faqs.edit')->with([
            'availables' => $availables,
            'categories' => $categories,
            'faq' => $faq,
        ]);
    }

    public function store(StoreFaqRequest $request)
    {

        $faq = new Faq;
        $faq->slack = $this->generate_slack('faqs');
        $faq->title = $request->title;
        $faq->description = $request->description;
        $faq->slug = Str::slug($request->title, '-');
        $faq->available = $request->available;
        $faq->category_id = $request->categorie;
        $faq->save();

        return response()->json([
            'success' => true,
            'message' => 'Se ha creado correctamente.',
        ]);

    }

    public function update(Request $request)
    {

        $faq = Faq::slack($request->slack);
        $faq->title = $request->title;
        $faq->description = $request->description;
        $faq->slug = Str::slug($request->title, '-');
        $faq->available = $request->available;
        $faq->category_id = $request->categorie;
        $faq->update();

        return response()->json([
            'success' => true,
            'message' => 'Se ha actualizado correctamente.',
        ]);

    }

    public function destroy($slack)
    {

        $faq = Faq::slack($slack);
        $faq->delete();

        return redirect()->route('support.faqs');
    }

    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'action' => ['required', 'in:delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:faqs,id'],
        ]);

        $query = Faq::whereIn('id', $request->ids);
        $count = $query->count();

        match ($request->action) {
            'delete' => $query->delete(),
        };

        return response()->json(['success' => true, 'message' => $count.' pregunta(s) procesadas.']);
    }
}
