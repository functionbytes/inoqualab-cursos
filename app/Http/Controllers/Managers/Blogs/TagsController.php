<?php

namespace App\Http\Controllers\Managers\Blogs;

use App\Http\Controllers\Controller;
use App\Models\Blog\BlogTag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TagsController extends Controller
{
    public function index(Request $request)
    {

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

    public function create()
    {

        $availables = $this->availableOptions();

        return view('managers.views.blogs.tags.create')->with([
            'availables' => $availables,
        ]);

    }

    public function view($slack)
    {

        $tag = BlogTag::slack($slack);

        return view('managers.views.blogs.tags.view')->with([
            'tag' => $tag,
        ]);

    }

    public function edit($slack)
    {

        $tag = BlogTag::slack($slack);

        $availables = $this->availableOptions();

        return view('managers.views.blogs.tags.edit')->with([
            'tag' => $tag,
            'availables' => $availables,
        ]);

    }

    public function update(Request $request)
    {

        $tag = BlogTag::slack($request->slack);
        $tag->title = $request->title;
        $tag->slug = Str::slug($request->title, '-');
        $tag->available = $request->available;
        $tag->update();

        return response()->json([
            'success' => true,
            'message' => 'Se actualizo la etiqueta correctamente',
        ]);

    }

    public function store(Request $request)
    {

        $tag = new BlogTag;
        $tag->slack = $this->generate_slack('blog_categories');
        $tag->title = $request->title;
        $tag->slug = Str::slug($request->title, '-');
        $tag->available = $request->available;
        $tag->save();

        return response()->json([
            'success' => true,
            'message' => 'Se creo la etiqueta correctamente',
        ]);

    }

    public function destroy($slack)
    {
        $tag = BlogTag::slack($slack);
        $tag->delete();

        return redirect()->route('manager.blogs.tags');
    }
}
