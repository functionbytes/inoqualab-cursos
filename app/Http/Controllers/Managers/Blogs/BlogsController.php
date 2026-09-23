<?php

namespace App\Http\Controllers\Managers\Blogs;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Blogs\BulkActionBlogRequest;
use App\Http\Requests\Managers\Blogs\StoreBlogRequest;
use App\Http\Requests\Managers\Blogs\StoreBlogThumbnailRequest;
use App\Http\Requests\Managers\Blogs\UpdateBlogRequest;
use App\Models\Blog\Blog;
use App\Models\Blog\BlogCategorie;
use App\Models\Blog\BlogTag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class BlogsController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('blogs.view'), 403);

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

        $view = request()->ajax() ? 'managers.views.blogs.blogs._table' : 'managers.views.blogs.blogs.index';

        return view($view)->with([
            'blogs' => $blogs,
            'available' => $available,
            'searchKey' => $searchKey,
        ]);

    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('blogs.create'), 403);

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

    public function view($slug): View
    {
        abort_unless(auth()->user()->can('blogs.view'), 403);

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

    public function edit($slack): View
    {
        abort_unless(auth()->user()->can('blogs.update'), 403);

        $blog = Blog::slack($slack);
        $blog->load('tags');

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
            // Solo los ids: es lo que Form::select necesita para marcar las
            // opciones ya asignadas al post.
            'selectedTags' => $blog->tags->pluck('id')->all(),
            'availables' => $availables,
            'thumbnail' => $thumbnail,
        ]);

    }

    public function update(UpdateBlogRequest $request): JsonResponse
    {
        $data = $request->validated();

        $blog = Blog::slack($data['slack']);

        DB::transaction(function () use ($data, $blog) {
            $blog->title = Str::upper($data['title']);
            $blog->slug = Str::slug($data['title'], '-');
            $blog->content = $data['contents'];
            $blog->description = $data['description'];
            $blog->date_at = $data['date'];
            $blog->categorie_id = $data['categorie'];
            $blog->available = $data['available'];
            $blog->update();

            if (! empty($data['tags'])) {
                $tagIds = array_filter(explode(',', $data['tags']));
                $blog->tags()->sync($tagIds);
            } else {
                $blog->tags()->detach();
            }
        });

        return response()->json([
            'success' => true,
            'slack' => $blog->slack,
            'message' => 'Se actualizó el blog correctamente',
        ]);

    }

    public function store(StoreBlogRequest $request): JsonResponse
    {
        $data = $request->validated();

        $blog = new Blog;

        DB::transaction(function () use ($data, $blog) {
            $blog->title = Str::upper($data['title']);
            $blog->slack = $this->generate_slack('blogs');
            $blog->slug = Str::slug($data['title'], '-');
            $blog->description = $data['description'];
            $blog->content = $data['contents'];
            $blog->available = $data['available'];
            $blog->categorie_id = $data['categorie'];
            $blog->date_at = $data['date'];
            $blog->save();

            if (! empty($data['tags'])) {
                $tagIds = array_filter(explode(',', $data['tags']));
                $blog->tags()->attach($tagIds);
            }
        });

        return response()->json([
            'success' => true,
            'slack' => $blog->slack,
            'message' => 'Se creó el blog correctamente',
        ]);

    }

    public function destroy($slack): RedirectResponse
    {
        abort_unless(auth()->user()->can('blogs.delete'), 403);

        $blog = Blog::slack($slack);
        $blog->delete();

        return redirect()->route('manager.blogs');

    }

    public function bulkAction(BulkActionBlogRequest $request): JsonResponse
    {
        $query = Blog::whereIn('id', $request->ids);
        $count = $query->count();

        match ($request->action) {
            'publish' => $query->update(['available' => 1]),
            'hide' => $query->update(['available' => 0]),
            'delete' => $query->delete(),
        };

        return response()->json(['success' => true, 'message' => $count.' noticia(s) procesadas.']);
    }

    public function getThumbnails($slack): JsonResponse
    {
        abort_unless(auth()->user()->can('blogs.update'), 403);

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

    public function storeThumbnails(StoreBlogThumbnailRequest $request): JsonResponse
    {
        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $blog = Blog::slack(Str::remove('"', $request->blog));
            $blog->addMediaFromRequest('file')->toMediaCollection('thumbnail');

            return response()->json(['status' => 'success', 'blog' => $blog->slack]);
        }

        return response()->json(['status' => 'error', 'message' => 'Archivo no válido.'], 422);
    }

    public function deleteThumbnails($id): JsonResponse
    {
        abort_unless(auth()->user()->can('blogs.update'), 403);

        Media::where('id', $id)
            ->where('model_type', Blog::class)
            ->where('collection_name', 'thumbnail')
            ->first()?->delete();

        return response()->json(['status' => 'success']);

    }
}
