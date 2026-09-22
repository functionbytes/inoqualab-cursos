<?php

namespace App\Http\Controllers\Managers\Blogs;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Blogs\StoreBlogCategoryRequest;
use App\Http\Requests\Managers\Blogs\UpdateBlogCategoryRequest;
use App\Models\Blog\BlogCategorie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoriesController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('blogs.view'), 403);

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

        $view = request()->ajax() ? 'managers.views.blogs.categories._table' : 'managers.views.blogs.categories.index';

        return view($view)->with([
            'categories' => $categories,
            'available' => $available,
            'searchKey' => $searchKey,
        ]);

    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('blogs.create'), 403);

        $availables = $this->availableOptions();

        return view('managers.views.blogs.categories.create')->with([
            'availables' => $availables,
        ]);

    }

    public function edit($slack): View
    {
        abort_unless(auth()->user()->can('blogs.update'), 403);

        $categorie = BlogCategorie::slack($slack);

        $availables = $this->availableOptions();

        return view('managers.views.blogs.categories.edit')->with([
            'categorie' => $categorie,
            'availables' => $availables,
        ]);

    }

    public function update(UpdateBlogCategoryRequest $request): JsonResponse
    {
        $data = $request->validated();

        $categorie = BlogCategorie::slack($data['slack']);
        $categorie->title = $data['title'];
        $categorie->slug = Str::slug($data['title'], '-');
        $categorie->available = $data['available'];
        $categorie->update();

        return response()->json([
            'success' => true,
            'message' => 'Se actualizo la categoria correctamente',
        ]);

    }

    public function store(StoreBlogCategoryRequest $request): JsonResponse
    {
        $data = $request->validated();

        $categorie = new BlogCategorie;
        $categorie->slack = $this->generate_slack('blog_categories');
        $categorie->title = $data['title'];
        $categorie->slug = Str::slug($data['title'], '-');
        $categorie->available = $data['available'];
        $categorie->save();

        return response()->json([
            'success' => true,
            'message' => 'Se creo la categoria correctamente',
        ]);

    }

    public function destroy($slack): RedirectResponse
    {
        abort_unless(auth()->user()->can('blogs.delete'), 403);

        $categorie = BlogCategorie::slack($slack);
        $categorie->delete();

        return redirect()->route('manager.blogs.categories');
    }

    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'action' => ['required', 'in:publish,hide,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:blog_categories,id'],
        ]);

        $permission = $request->action === 'delete' ? 'blogs.delete' : 'blogs.update';
        abort_unless(auth()->user()->can($permission), 403);

        $query = BlogCategorie::whereIn('id', $request->ids);
        $count = $query->count();

        match ($request->action) {
            'publish' => $query->update(['available' => 1]),
            'hide' => $query->update(['available' => 0]),
            'delete' => $query->delete(),
        };

        return response()->json(['success' => true, 'message' => $count.' categoria(s) procesadas.']);
    }
}
