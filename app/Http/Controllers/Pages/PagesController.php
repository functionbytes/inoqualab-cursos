<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Bundle\Bundle;
use App\Models\Course\Course;
use App\Models\Enterprise\Enterprise;
use App\Models\Faq\Faq;
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
            'bundles' => Bundle::available()->latest()->withCount('courses')->limit(4)->get(),
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
        return redirect()->route(User::auth()->redirect());
    }

    public function about()
    {
        seo()->setTitle('Sobre nosotros')->setCanonical(url()->current());

        return view('pages.views.about')->with([
            'enterprises' => Enterprise::count(),
            'users' => User::count(),
        ]);
    }

    public function faqs()
    {
        $faqs = Faq::get();

        seo()->setTitle('Preguntas frecuentes')
            ->setCanonical(url()->current())
            ->setSchema(app(SchemaOrgService::class)->faq(
                $faqs->map(fn ($faq) => [
                    'question' => $faq->title,
                    'answer' => trim(preg_replace('/\s+/', ' ', strip_tags((string) $faq->description))),
                ])->all()
            ));

        return view('pages.views.faqs')->with([
            'faqs' => $faqs,
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
