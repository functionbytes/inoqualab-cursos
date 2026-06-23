<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Bundle\Bundle;
use App\Models\Course\Course;
use App\Models\Course\CourseCategorie;
use App\Models\Inscription;
use App\Services\SchemaOrgService;
use Illuminate\Http\Request;

class CoursesController extends Controller
{
    public function index(Request $request)
    {
        seo()->setCanonical(url()->current());

        $courses = Course::available()->latest()->website()
            ->with(['categorie', 'media'])
            ->withCount(['lessons', 'chapters'])
            ->get();

        $categories = CourseCategorie::available()->get();
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

        abort_unless($course instanceof Course, 404);

        $course->load(['seoMeta', 'media', 'categorie', 'chapters']);

        $courseImage = $course->getFirstMediaUrl('thumbnail') ?: getMeta();

        seo()->loadFromModel($course)
            ->setOgType('article')
            ->setOgImage($courseImage)
            ->setSchema(app(SchemaOrgService::class)->course($course));

        $categorie = $course->categorie;
        $chapters = $course->chapters;
        $leasons = $course->lessons()->orderBy('position', 'ASC')->get();

        $relateds = $categorie
            ? $categorie->courses()->withCount(['lessons', 'chapters'])->get()->shuffle()->take(3)
            : collect();

        $categories = CourseCategorie::available()->get();

        $reviews = $course->reviews()->with('user')->whereNotNull('comment')
            ->where('comment', '!=', '')->latest()->limit(12)->get();
        $reviewsCount = $course->reviews()->count();

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
        $categorie = CourseCategorie::slug($slug);

        abort_unless($categorie instanceof CourseCategorie, 404);

        seo()->setTitle($categorie->title)->setCanonical(url()->current());

        $courses = $categorie->courses()->available()->latest()->website()->with(['categorie', 'media'])->withCount(['lessons', 'chapters']);
        $courses = $courses->paginate(paginationNumber());

        $categories = CourseCategorie::select('id', 'title', 'slug')->orderBy('title')->get();
        $recents = Course::latest()->available()->website()->with(['categorie', 'media'])->withCount(['lessons', 'chapters'])->limit(3)->get();
        $populars = Course::latest()->available()->website()->with(['categorie', 'media'])->withCount(['lessons', 'chapters'])->limit(2)->get();

        return view('pages.views.courses.index')->with([
            'courses' => $courses,
            'populars' => $populars,
            'categories' => $categories,
            'recents' => $recents,
        ]);
    }
}
