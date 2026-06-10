<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Bundle\Bundle;
use App\Models\Course\Course;
use App\Models\Course\CourseCategorie;
use App\Models\Inscription;
use Artesaos\SEOTools\Facades\JsonLd;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CoursesController extends Controller
{
    public function index(Request $request)
    {

        $searchKey = $request->search;
        $categorie = $request->categorie;
        $filter = $request->filter;

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

        // El catálogo filtra/ordena del lado del cliente (diseño "Cursos"),
        // por lo que se cargan todos los cursos disponibles de una vez.
        $courses = Course::available()->latest()->website()
            ->with(['categorie', 'media'])
            ->withCount(['lessons', 'chapters'])
            ->get();

        $categories = CourseCategorie::available()->get();

        // Paquetes destacados para la franja del catálogo
        $bundles = Bundle::available()->latest()->withCount('courses')->limit(4)->get();

        return view('pages.views.courses.index')->with([
            'courses' => $courses,
            'categories' => $categories,
            'bundles' => $bundles,
        ]);
    }

    public function view($slack)
    {

        $course = Course::slack($slack);

        // OJO: el scope slack() termina en ->first(); cuando no hay match Laravel
        // convierte ese null en el query Builder (callScope: `?? $this`), por lo que
        // hay que comprobar el tipo y no solo si es "falsy".
        abort_unless($course instanceof Course, 404);

        $courseTitle = $course->title.' | '.getSetting()->meta_title;
        $courseDescription = strip_tags(Str::limit($course->short_detail ?: '', 160));
        $courseImage = $course->getFirstMediaUrl('thumbnail') ?: getMeta();
        $courseUrl = url()->current();

        SEOMeta::setTitle($courseTitle);
        SEOMeta::setDescription($courseDescription);
        SEOMeta::setCanonical($courseUrl);

        SEOTools::setTitle($courseTitle);
        SEOTools::setDescription($courseDescription);
        SEOTools::opengraph()->setUrl($courseUrl);
        SEOTools::setCanonical($courseUrl);
        SEOTools::opengraph()->addProperty('type', 'article');
        SEOTools::twitter()->setSite('@bpmsandiego');
        SEOTools::jsonLd()->addImage($courseImage);

        OpenGraph::setTitle($courseTitle);
        OpenGraph::setDescription($courseDescription);
        OpenGraph::setUrl($courseUrl);
        OpenGraph::addProperty('type', 'article');
        OpenGraph::addProperty('locale', 'es-CO');
        OpenGraph::addImage($courseImage);

        JsonLd::setTitle($courseTitle);
        JsonLd::setDescription($courseDescription);
        JsonLd::addImage($courseImage);

        $categorie = $course->categorie;

        $chapters = $course->chapters;
        $leasons = $course->lessons()->orderBy('position', 'ASC')->get();

        // Un curso podría no tener categoría asociada: evitar el fatal error.
        $relateds = $categorie
            ? $categorie->courses()->withCount(['lessons', 'chapters'])->get()->shuffle()->take(3)
            : collect();

        $categories = CourseCategorie::available()->get();

        // Opiniones de estudiantes (solo las que tienen comentario), con su autor.
        $reviews = $course->reviews()->with('user')->whereNotNull('comment')
            ->where('comment', '!=', '')->latest()->limit(12)->get();
        $reviewsCount = $course->reviews()->count();

        // Detectar si el usuario autenticado ya adquirió este curso.
        // Usamos inscriptions directamente: se crean solo tras pago exitoso.
        $alreadyOwned = false;
        $inscriptionSlack = null;
        if (auth()->check()) {
            $inscription = Inscription::where('user_id', auth()->id())
                ->where('course_id', $course->id)
                ->latest('id')
                ->first();
            $alreadyOwned = $inscription !== null;
            $inscriptionSlack = $inscription?->slack;
        }

        return view('pages.views.courses.view')->with([
            'course' => $course,
            'chapters' => $chapters,
            'categories' => $categories,
            'relateds' => $relateds,
            'leasons' => $leasons,
            'reviews' => $reviews,
            'reviewsCount' => $reviewsCount,
            'alreadyOwned' => $alreadyOwned,
            'inscriptionSlack' => $inscriptionSlack,
        ]);

    }

    public function categories($slug)
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

        $categorie = CourseCategorie::slug($slug);

        // Mismo caso que en view(): el scope slug() puede devolver el Builder
        // cuando no hay match, por eso comprobamos el tipo concreto.
        abort_unless($categorie instanceof CourseCategorie, 404);

        $courses = $categorie->courses()->available()->latest()->website()->withCount(['lessons', 'chapters']);
        $courses = $courses->paginate(paginationNumber());

        $categories = CourseCategorie::select('id', 'title', 'slug')->orderBy('title')->get();
        $recents = Course::latest()->available()->website()->withCount(['lessons', 'chapters'])->limit(3)->get();
        $populars = Course::latest()->available()->website()->withCount(['lessons', 'chapters'])->limit(2)->get();

        return view('pages.views.courses.index')->with([
            'courses' => $courses,
            'populars' => $populars,
            'categories' => $categories,
            'recents' => $recents,
        ]);

    }
}
