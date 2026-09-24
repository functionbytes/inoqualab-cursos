<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Bundle\Bundle;
use App\Models\Course\Course;
use App\Models\Enterprise\Enterprise;
use App\Models\Faq\Faq;
use App\Models\Faq\FaqCategorie;
use App\Models\Testimonie;
use App\Models\User;
use App\Services\SchemaOrgService;
use Illuminate\Support\Facades\Cache;

class PagesController extends Controller
{
    public function index()
    {
        seo()->setCanonical(url('/'))
            ->setSchema(app(SchemaOrgService::class)->organization());

        // Catálogo del home: cambia con poca frecuencia y es la página de mayor
        // tráfico. Se invalida al guardar/borrar Course/Bundle/CourseCategorie
        // (AppServiceProvider::registerCatalogCacheInvalidation).
        $catalog = Cache::remember('catalog.home', now()->addMinutes(15), fn () => [
            'courses' => Course::latest()->available()->two(5)->website()->with(['categorie', 'media'])->withCount(['lessons', 'chapters'])->get(),
            'bpms' => Course::latest()->available()->one(23)->website()->with(['categorie', 'media'])->withCount(['lessons', 'chapters'])->get(),
            'populars' => Course::latest()->available()->featured()->website()->with(['categorie', 'media'])->withCount(['lessons', 'chapters'])->get(),
            'bundles' => Bundle::available()->latest()->withCount('courses')->with('courses.media')->limit(4)->get(),
            'homeFaqs' => Faq::take(5)->get(),
            // limit(12), no 6: los testimonios previos a esta feature (sin position
            // ni fecha reciente) empatan en position=0 con los nuevos y, ordenados
            // por created_at, ya llenaban el límite antes de llegar a estos últimos.
            'testimonials' => Testimonie::available()->ordered()->limit(12)->get(),
        ]);

        return view('pages.views.index')->with($catalog);
    }

    public function coming()
    {
        seo()->setTitle('Próximamente')->setCanonical(url()->current());

        return view('pages.views.comings');
    }

    public function home()
    {
        // route('home') se usa como "volver al inicio" desde varias vistas
        // públicas (confirmación/baja de newsletter, cuenta deshabilitada,
        // verificación de email) donde el visitante NO está autenticado.
        // User::auth() es Auth::user(), que ahí es null -- sin este guard,
        // ->redirect() sobre null era un error fatal ("Call to a member
        // function redirect() on null") en vez de llevarlo al home público.
        $user = User::auth();

        if ($user === null) {
            return redirect()->route('index');
        }

        return redirect()->route($user->redirect());
    }

    public function about()
    {
        seo()->setTitle('Sobre nosotros')->setCanonical(url()->current());

        // Diseño elegido en Configuración › Sitio web ('1' = el original).
        // Un manager con sesión puede previsualizar otro con ?diseno=a|b|c|d sin
        // cambiar lo que ven los visitantes.
        $views = [
            '1' => 'pages.views.about',
            'a' => 'pages.views.about.informe',
            'b' => 'pages.views.about.petri',
            'c' => 'pages.views.about.norma',
            'd' => 'pages.views.about.combinada',
        ];
        $variant = (string) setting('pages_about_variant', '1');
        $preview = (string) request()->query('diseno', '');
        if ($preview !== '' && isset($views[$preview]) && auth()->user()?->role === 'manager') {
            $variant = $preview;
        }

        return view($views[$variant] ?? $views['1'])->with([
            'enterprises' => Enterprise::count(),
            'users' => User::count(),
        ]);
    }

    public function faqs()
    {
        $categories = FaqCategorie::with(['faqs' => fn ($query) => $query->orderBy('id')])
            ->get()
            ->filter(fn ($categorie) => $categorie->faqs->isNotEmpty());

        seo()->setTitle('Preguntas frecuentes')
            ->setCanonical(url()->current())
            ->setSchema(app(SchemaOrgService::class)->faq(
                $categories->flatMap->faqs->map(fn ($faq) => [
                    'question' => $faq->title,
                    'answer' => trim(preg_replace('/\s+/', ' ', strip_tags((string) $faq->description))),
                ])->all()
            ));

        return view('pages.views.faqs')->with([
            'categories' => $categories,
        ]);
    }

    public function politics()
    {
        seo()->setTitle('Política de privacidad')->setCanonical(url()->current());

        return view('pages.views.politics');
    }

    public function terms()
    {
        seo()->setTitle('Términos y condiciones')->setCanonical(url()->current());

        return view('pages.views.terms');
    }
}
