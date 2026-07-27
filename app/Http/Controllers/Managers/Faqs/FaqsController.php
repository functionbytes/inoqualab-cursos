<?php

namespace App\Http\Controllers\Managers\Faqs;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Faqs\StoreFaqRequest;
use App\Models\Faq\Faq;
use App\Models\Faq\FaqCategorie;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FaqsController extends Controller
{
    public function index(Request $request)
    {

        $searchKey = $request->search;
        $available = $request->available;

        $faqs = Faq::descending()->with('categorie');

        if ($searchKey) {
            $faqs = $faqs->where('title', 'like', '%'.$searchKey.'%');
        }

        if ($request->available != null) {
            $faqs = $faqs->where('available', $available);
        }

        $faqs = $faqs->paginate(paginationNumber());

        return view('managers.views.settings.faqs.faqs.index')->with([
            'faqs' => $faqs,
            'available' => $available,
            'searchKey' => $searchKey,
        ]);
    }

    public function create()
    {

        $availables = $this->availableOptions();

        $categories = FaqCategorie::latest()->available()->get();
        $categories->prepend('', '');
        $categories = $categories->pluck('title', 'id');

        return view('managers.views.settings.faqs.faqs.create')->with([
            'availables' => $availables,
            'categories' => $categories,
        ]);

    }

    public function edit($slack)
    {

        $faq = Faq::slack($slack);

        $availables = $this->availableOptions();

        $categories = FaqCategorie::latest()->available()->get();
        $categories = $categories->pluck('title', 'id');

        return view('managers.views.settings.faqs.faqs.edit')->with([
            'availables' => $availables,
            'categories' => $categories,
            'faq' => $faq,
        ]);
    }

    public function store(StoreFaqRequest $request)
    {
        abort_unless(auth()->user()->can('faqs.create'), 403);

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
            'message' => 'Se ha creado correctamente',
        ]);

    }

    public function update(Request $request)
    {
        abort_unless(auth()->user()->can('faqs.update'), 403);

        $faq = Faq::slack($request->slack);
        $faq->title = $request->title;
        $faq->description = $request->description;
        $faq->slug = Str::slug($request->title, '-');
        $faq->available = $request->available;
        $faq->category_id = $request->categorie;
        $faq->update();

        return response()->json([
            'success' => true,
            'message' => 'Se ha actualizo correctamente',
        ]);

    }

    public function destroy($slack)
    {
        abort_unless(auth()->user()->can('faqs.delete'), 403);

        $faq = Faq::slack($slack);
        $faq->delete();

        return redirect()->route('manager.faqs');
    }
}
