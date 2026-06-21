<?php

namespace App\Http\Controllers\Managers\Blogs;

use App\Http\Controllers\Controller;
use App\Models\Blog\BlogCategorie;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoriesController extends Controller
{
    public function index(Request $request)
    {

        $searchKey = $request->search;
        $available = $request->available;

        $categories = BlogCategorie::descending();

        if ($searchKey) {
            $categories = $categories->where('title', 'like', '%'.$searchKey.'%');
        }

        if ($request->available != null) {
            $categories = $categories->where('available', $available);
        }

        $categories = $categories->paginate(paginationNumber());

        return view('managers.views.blogs.categories.index')->with([
            'categories' => $categories,
            'available' => $available,
            'searchKey' => $searchKey,
        ]);

    }

    public function create()
    {

        $availables = $this->availableOptions();

        return view('managers.views.blogs.categories.create')->with([
            'availables' => $availables,
        ]);

    }

    public function view($slack)
    {

        $categorie = BlogCategorie::slack($slack);

        return view('managers.views.blogs.categories.view')->with([
            'categorie' => $categorie,
        ]);

    }

    public function edit($slack)
    {

        $categorie = BlogCategorie::slack($slack);

        $availables = $this->availableOptions();

        return view('managers.views.blogs.categories.edit')->with([
            'categorie' => $categorie,
            'availables' => $availables,
        ]);

    }

    public function update(Request $request)
    {
        abort_unless(auth()->user()->can('blogs.update'), 403);

        $categorie = BlogCategorie::slack($request->slack);
        $categorie->title = $request->title;
        $categorie->slug = Str::slug($request->title, '-');
        $categorie->available = $request->available;
        $categorie->update();

        return response()->json([
            'success' => true,
            'message' => 'Se actualizo la categoria correctamente',
        ]);

    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('blogs.create'), 403);

        $categorie = new BlogCategorie;
        $categorie->slack = $this->generate_slack('blog_categories');
        $categorie->title = $request->title;
        $categorie->slug = Str::slug($request->title, '-');
        $categorie->available = $request->available;
        $categorie->save();

        return response()->json([
            'success' => true,
            'message' => 'Se creo la categoria correctamente',
        ]);

    }

    public function destroy($slack)
    {
        abort_unless(auth()->user()->can('blogs.delete'), 403);

        $categorie = BlogCategorie::slack($slack);
        $categorie->delete();

        return redirect()->route('manager.blogs.categories');
    }
}
