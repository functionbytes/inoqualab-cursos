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
    /**
     * Datos de la barra lateral, compartidos por el listado y sus variantes.
     *
     * El conteo de posts por categoría se hace con withCount y filtrado por
     * `available`: el widget iteraba `count($categorie->blogs)` dentro del
     * bucle, lo que disparaba una consulta por categoría y además contaba
     * también los borradores, así que el número no cuadraba con la lista.
     */
    private function sidebar(): array
    {
        return [
            'categories' => BlogCategorie::select('id', 'title', 'slug')
                ->withCount(['blogs' => fn ($q) => $q->where('available', 1)])
                ->orderBy('title')
                ->get(),
            'tags' => BlogTag::select('id', 'title', 'slug')->orderBy('title')->get(),
            'recents' => Blog::with('media')->latest()->available()->limit(2)->get(),
        ];
    }

    public function index(Request $request)
    {

        seo()->setTitle('Blog')->setCanonical(url()->current());

        $searchKey = $request->search;
        $categorie = $request->categorie;
        $tag = $request->tag;

        $blogs = Blog::with('media')->latest()->available();

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
                $blogs = $tag->blogs()->with('media')->latest()->available();
            }
        }

        return view('pages.views.blogs.index')->with([
            'blogs' => $blogs->paginate(paginationNumber())->withQueryString(),
            'search' => $searchKey,
        ] + $this->sidebar());

    }

    /**
     * Buscador lateral del blog.
     *
     * La ruta existía y el widget de búsqueda publicaba contra ella
     * (`Form::open(['route' => ['blogs.filters']])`), pero el método no estaba
     * implementado: buscar en el blog devolvía un 500.
     *
     * Se redirige al listado con el término en la query string en vez de
     * renderizar aquí, para que el resultado se pueda enlazar, recargar y
     * paginar sin reenviar el formulario.
     */
    public function filters(Request $request)
    {
        $search = trim((string) $request->input('search'));

        return redirect()->route('blogs', $search !== '' ? ['search' => $search] : []);
    }

    /**
     * Listado filtrado por categoría.
     *
     * La ruta `blogs.categories` existía y las vistas la enlazaban (la ficha de
     * un post y el widget de categorías), pero el método nunca se implementó:
     * cualquier visitante que pulsara una categoría recibía un 500.
     */
    public function categories(Request $request, string $slug)
    {
        $categorie = BlogCategorie::where('slug', $slug)->firstOrFail();

        seo()->setTitle('Blog · '.$categorie->title)->setCanonical(url()->current());

        $blogs = Blog::with('media')->latest()->available()->where('categorie_id', $categorie->id);

        return view('pages.views.blogs.index')->with([
            'blogs' => $blogs->paginate(paginationNumber())->withQueryString(),
            'categorie' => $categorie,
        ] + $this->sidebar());
    }

    /**
     * Listado filtrado por etiqueta. Mismo caso que categories(): la ruta se
     * enlazaba desde la ficha del post y el método no existía.
     */
    public function tags(Request $request, string $slug)
    {
        $tag = BlogTag::where('slug', $slug)->firstOrFail();

        seo()->setTitle('Blog · '.$tag->title)->setCanonical(url()->current());

        $blogs = $tag->blogs()->with('media')->latest()->available();

        return view('pages.views.blogs.index')->with([
            'blogs' => $blogs->paginate(paginationNumber())->withQueryString(),
            'tag' => $tag,
        ] + $this->sidebar());
    }

    public function view($slug)
    {

        $blog = Blog::slug($slug);

        // El scope slug() devuelve el Builder cuando no hay match: comprobar tipo.
        abort_unless($blog instanceof Blog, 404);

        $blog->load('seoMeta');

        $blogImage = $blog->getFirstMediaUrl('thumbnail') ?: getMeta();

        $schema = app(SchemaOrgService::class);

        seo()->loadFromModel($blog)
            ->setOgType('article')
            ->setOgImage($blogImage)
            ->setSchema($schema->article($blog))
            ->addSchema($schema->breadcrumbs([
                ['name' => 'Inicio', 'url' => url('/')],
                ['name' => 'Blog', 'url' => route('blogs')],
                ['name' => $blog->title, 'url' => url()->current()],
            ]));

        // Relacionados de la misma categoría, excluyendo el post que se está
        // leyendo: antes aparecía él mismo en su propia lista de "relacionados".
        $relateds = Blog::with('media')
            ->where('categorie_id', $blog->categorie_id)
            ->where('id', '!=', $blog->id)
            ->available()
            ->latest()
            ->take(3)
            ->get();

        return view('pages.views.blogs.view')->with([
            // La ficha pinta la categoría y las etiquetas del post.
            'blog' => $blog->load(['categorie', 'tags']),
            'relateds' => $relateds,
            'views' => 0,
            'comments' => [],
        ] + $this->sidebar());

    }
}
