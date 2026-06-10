<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Blog\Blog;
use App\Models\Blog\BlogCategorie;
use App\Models\Blog\BlogTag;
use Artesaos\SEOTools\Facades\JsonLd;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function index(Request $request)
    {

        SEOMeta::setTitle(getSetting()->meta_title);
        SEOMeta::setDescription(getSetting()->meta_description);
        SEOMeta::setCanonical(getUrl());

        SEOTools::setTitle(getSetting()->meta_title);
        SEOTools::setDescription(getSetting()->meta_description);
        SEOTools::opengraph()->setUrl(getUrl());
        SEOTools::setCanonical(getUrl());
        SEOTools::opengraph()->addProperty('type', 'articles');
        SEOTools::twitter()->setSite('@bpmsandiego');
        SEOTools::jsonLd()->addImage(getMeta());

        OpenGraph::setTitle(getSetting()->meta_title);
        OpenGraph::setDescription(getSetting()->meta_description);
        OpenGraph::setUrl(getUrl());
        OpenGraph::addProperty('type', 'article');
        OpenGraph::addProperty('locale', 'en-En');
        OpenGraph::addImage(getMeta());

        JsonLd::setTitle(getSetting()->meta_title);
        JsonLd::setDescription(getSetting()->meta_description);
        JsonLd::addImage(getMeta());

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

        $blogTitle = $blog->title.' | '.getSetting()->meta_title;
        $blogDescription = strip_tags(Str::limit($blog->short_detail ?: $blog->content, 160));
        $blogImage = $blog->getFirstMediaUrl('thumbnail') ?: getMeta();
        $blogUrl = url()->current();

        SEOMeta::setTitle($blogTitle);
        SEOMeta::setDescription($blogDescription);
        SEOMeta::setCanonical($blogUrl);

        SEOTools::setTitle($blogTitle);
        SEOTools::setDescription($blogDescription);
        SEOTools::opengraph()->setUrl($blogUrl);
        SEOTools::setCanonical($blogUrl);
        SEOTools::opengraph()->addProperty('type', 'article');
        SEOTools::twitter()->setSite('@bpmsandiego');
        SEOTools::jsonLd()->addImage($blogImage);

        OpenGraph::setTitle($blogTitle);
        OpenGraph::setDescription($blogDescription);
        OpenGraph::setUrl($blogUrl);
        OpenGraph::addProperty('type', 'article');
        OpenGraph::addProperty('locale', 'es-CO');
        OpenGraph::addImage($blogImage);

        JsonLd::setTitle($blogTitle);
        JsonLd::setDescription($blogDescription);
        JsonLd::addImage($blogImage);

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
