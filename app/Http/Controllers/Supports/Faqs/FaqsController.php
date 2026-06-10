<?php

namespace App\Http\Controllers\Supports\Faqs;

use App\Http\Controllers\Controller;
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

        $faqs = Faq::descending();

        if ($searchKey) {
            $faqs = $faqs->where('title', 'like', '%'.$searchKey.'%');
        }

        if ($request->available != null) {
            $faqs = $faqs->where('available', $available);
        }

        $faqs = $faqs->paginate(paginationNumber());

        return view('supports.views.faqs.faqs.index')->with([
            'faqs' => $faqs,
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

    public function store(Request $request)
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
}
