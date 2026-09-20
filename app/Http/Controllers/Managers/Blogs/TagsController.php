<?php

namespace App\Http\Controllers\Managers\Blogs;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Blogs\StoreBlogTagRequest;
use App\Http\Requests\Managers\Blogs\UpdateBlogTagRequest;
use App\Models\Blog\BlogTag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TagsController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('blogs.view'), 403);

        $searchKey = $request->search;
        $available = $request->available;

        $tags = BlogTag::descending();

        if ($searchKey) {
            $tags = $tags->where('title', 'like', '%'.$searchKey.'%');
        }

        if ($request->available != null) {
            $tags = $tags->where('available', $available);
        }

        $tags = $tags->paginate(paginationNumber());

        return view('managers.views.blogs.tags.index')->with([
            'tags' => $tags,
            'available' => $available,
            'searchKey' => $searchKey,
        ]);
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('blogs.create'), 403);

        $availables = $this->availableOptions();

        return view('managers.views.blogs.tags.create')->with([
            'availables' => $availables,
        ]);

    }

    public function view($slack): View
    {
        abort_unless(auth()->user()->can('blogs.view'), 403);

        $tag = BlogTag::slack($slack);

        return view('managers.views.blogs.tags.view')->with([
            'tag' => $tag,
        ]);

    }

    public function edit($slack): View
    {
        abort_unless(auth()->user()->can('blogs.update'), 403);

        $tag = BlogTag::slack($slack);

        $availables = $this->availableOptions();

        return view('managers.views.blogs.tags.edit')->with([
            'tag' => $tag,
            'availables' => $availables,
        ]);

    }

    public function update(UpdateBlogTagRequest $request): JsonResponse
    {
        $data = $request->validated();

        $tag = BlogTag::slack($data['slack']);
        $tag->title = $data['title'];
        $tag->slug = Str::slug($data['title'], '-');
        $tag->available = $data['available'];
        $tag->update();

        return response()->json([
            'success' => true,
            'message' => 'Se actualizo la etiqueta correctamente',
        ]);

    }

    public function store(StoreBlogTagRequest $request): JsonResponse
    {
        $data = $request->validated();

        $tag = new BlogTag;
        $tag->slack = $this->generate_slack('blog_tags');
        $tag->title = $data['title'];
        $tag->slug = Str::slug($data['title'], '-');
        $tag->available = $data['available'];
        $tag->save();

        return response()->json([
            'success' => true,
            'message' => 'Se creo la etiqueta correctamente',
        ]);

    }

    public function destroy($slack): RedirectResponse
    {
        abort_unless(auth()->user()->can('blogs.delete'), 403);
        $tag = BlogTag::slack($slack);
        $tag->delete();

        return redirect()->route('manager.blogs.tags');
    }

    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'action' => ['required', 'in:publish,hide,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:blog_tags,id'],
        ]);

        $permission = $request->action === 'delete' ? 'blogs.delete' : 'blogs.update';
        abort_unless(auth()->user()->can($permission), 403);

        $query = BlogTag::whereIn('id', $request->ids);
        $count = $query->count();

        match ($request->action) {
            'publish' => $query->update(['available' => 1]),
            'hide' => $query->update(['available' => 0]),
            'delete' => $query->delete(),
        };

        return response()->json(['success' => true, 'message' => $count.' etiqueta(s) procesadas.']);
    }
}
