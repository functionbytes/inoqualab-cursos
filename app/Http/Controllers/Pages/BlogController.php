<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Blog\Blog;
use App\Models\Blog\BlogCategorie;
use App\Models\Blog\BlogTag;
use App\Services\SchemaOrgService;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {

        seo()->setCanonical(url()->current());

        $searchKey = $request->search;
        $categorie = $request->categorie;
        $tag = $request->tag;

        $blogs = Blog::latest()->available();

        if ($searchKey) {
            $blogs = $blogs->where('title', 'like', '%'.$searchKey.'%');
        }

        if ($request->categorie != null) {
            $blogs = $blogs->where('categorie_id', $categorie);
        }

        if ($request->tag != null) {
            $tag = BlogTag::slug($tag);
            // Solo aplicar el filtro si el tag existe (el scope devuelve Builder si no).
            if ($tag instanceof BlogTag) {
                $blogs = $tag->blogs();
            }
        }

        $categories = BlogCategorie::select('id', 'title', 'slug')->orderBy('title')->get();
        $tags = BlogTag::select('id', 'title', 'slug')->orderBy('title')->get();
        $recents = Blog::latest()->available()->limit(2)->get();
        $blogs = $blogs->paginate(paginationNumber());

        return view('pages.views.blogs.index')->with([
            'blogs' => $blogs,
            'tags' => $tags,
            'recents' => $recents,
            'categories' => $categories,
        ]);

    }

    public function view($slug)
    {

        $blog = Blog::slug($slug);

        // El scope slug() devuelve el Builder cuando no hay match: comprobar tipo.
        abort_unless($blog instanceof Blog, 404);

        $blog->load('seoMeta');

        $blogImage = $blog->getFirstMediaUrl('thumbnail') ?: getMeta();

        seo()->loadFromModel($blog)
            ->setOgType('article')
            ->setOgImage($blogImage)
            ->setSchema(app(SchemaOrgService::class)->article($blog));

        $categories = BlogCategorie::select('id', 'title', 'slug')->orderBy('title')->get();
        $recents = Blog::latest()->limit(2)->get();
        $relateds = Blog::where('categorie_id', $blog->categorie_id)->available()->orderBy('created_at', 'desc')->take(3)->get();
        $tags = BlogTag::select('id', 'title', 'slug')->orderBy('title')->get();

        $comments = [];

        return view('pages.views.blogs.view')->with([
            'blog' => $blog,
            'categories' => $categories,
            'tags' => $tags,
            'recents' => $recents,
            'relateds' => $relateds,
            'views' => 0,
            'comments' => $comments,
        ]);

    }
}
