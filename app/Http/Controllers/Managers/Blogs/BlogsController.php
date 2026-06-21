<?php

namespace App\Http\Controllers\Managers\Blogs;

use App\Http\Controllers\Controller;
use App\Models\Blog\Blog;
use App\Models\Blog\BlogCategorie;
use App\Models\Blog\BlogTag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class BlogsController extends Controller
{
    public function index(Request $request)
    {

        $searchKey = $request->search;
        $available = $request->available;

        $blogs = Blog::descending()->with('categorie');

        if ($searchKey) {
            $blogs = $blogs->where('title', 'like', '%'.$searchKey.'%');
        }

        if ($request->available != null) {
            $blogs = $blogs->where('available', $available);
        }

        $blogs = $blogs->paginate(paginationNumber());

        return view('managers.views.blogs.blogs.index')->with([
            'blogs' => $blogs,
            'available' => $available,
            'searchKey' => $searchKey,
        ]);

    }

    public function create()
    {

        $categories = BlogCategorie::latest()->get();
        $categories->prepend('', '');
        $categories = $categories->pluck('title', 'id');

        $tags = BlogTag::latest()->get();
        $tags = $tags->pluck('title', 'id');

        $availables = $this->availableOptions();

        return view('managers.views.blogs.blogs.create')->with([
            'availables' => $availables,
            'categories' => $categories,
            'tags' => $tags,
        ]);

    }

    public function view($slug)
    {

        $blog = Blog::slug($slug);
        $categorie = $blog->categorie_id;

        $categories = BlogCategorie::latest()->available()->get();
        $categories->prepend('', '');
        $categories = $categories->pluck('title', 'id');

        $tags = BlogTag::latest()->get();
        $tags = $tags->pluck('title', 'id');

        return view('managers.views.blogs.blogs.view')->with([
            'blog' => $blog,
            'categorie' => $categorie,
            'categories' => $categories,
            'tags' => $tags,
        ]);

    }

    public function edit($slack)
    {

        $blog = Blog::slack($slack);

        $categories = BlogCategorie::latest()->available()->get();
        $categories->prepend('', '');
        $categories = $categories->pluck('title', 'id');

        $availables = $this->availableOptions();

        $tags = BlogTag::latest()->get();
        $tags = $tags->pluck('title', 'id');

        $thumbnail = $blog->getMedia('thumbnail')->count() > 0 ? 'true' : 'false';

        return view('managers.views.blogs.blogs.edit')->with([
            'blog' => $blog,
            'categories' => $categories,
            'tags' => $tags,
            'availables' => $availables,
            'thumbnail' => $thumbnail,
        ]);

    }

    public function update(Request $request)
    {
        abort_unless(auth()->user()->can('blogs.update'), 403);

        $blog = Blog::slack($request->slack);
        $blog->title = Str::upper($request->title);
        $blog->slug = Str::slug($request->title, '-');
        $blog->content = $request->contents;
        $blog->description = $request->description;
        $blog->date_at = $request->date;
        $blog->categorie_id = $request->categorie;
        $blog->available = $request->available;
        $blog->update();

        if ($request->has('tags')) {
            $tagIds = array_filter(explode(',', $request->tags));
            $blog->tags()->sync($tagIds);
        } else {
            $blog->tags()->detach();
        }

        return response()->json([
            'success' => true,
            'slack' => $blog->slack,
            'message' => 'Se actualizo el blog correctamente',
        ]);

    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('blogs.create'), 403);

        $blog = new Blog;
        $blog->title = Str::upper($request->title);
        $blog->slack = $this->generate_slack('blogs');
        $blog->slug = Str::slug($request->title, '-');
        $blog->description = $request->description;
        $blog->content = $request->contents;
        $blog->available = $request->available;
        $blog->categorie_id = $request->categorie;
        $blog->date_at = $request->date;
        $blog->save();

        if ($request->has('tags')) {
            $tagIds = array_filter(explode(',', $request->tags));
            $blog->tags()->attach($tagIds);
        }

        return response()->json([
            'success' => true,
            'slack' => $blog->slack,
            'message' => 'Se creo el blog correctamente',
        ]);

    }

    public function destroy($slack)
    {
        abort_unless(auth()->user()->can('blogs.delete'), 403);

        $blog = Blog::slack($slack);
        $blog->delete();

        return redirect()->route('manager.blogs');

    }

    public function getThumbnails($slack)
    {

        $blog = Blog::slack($slack);

        if ($blog->getMedia('thumbnail')->count() > 0) {

            $thumbnails = $blog->getMedia('thumbnail');

            foreach ($thumbnails as $thumbnail) {

                $images[] = [
                    'id' => $thumbnail->id,
                    'uuid' => $thumbnail->uuid,
                    'name' => $thumbnail->name,
                    'file' => $thumbnail->file_name,
                    'path' => $thumbnail->getfullUrl(),
                    'size' => $thumbnail->size,
                ];
            }

            return response()->json($images);
        }

        $images = [];

        return response()->json($images);

    }

    public function storeThumbnails(Request $request)
    {

        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $blog = Blog::slack(Str::remove('"', $request->blog));
            $blog->addMediaFromRequest('file')->toMediaCollection('thumbnail');

            return response()->json(['status' => 'success', 'blog' => $blog->slack]);
        }

        return response()->json(['status' => 'error', 'message' => 'Archivo no válido.'], 422);
    }

    public function deleteThumbnails($id)
    {

        Media::find($id)->delete();

        return response()->json(['status' => 'success']);

    }
}
