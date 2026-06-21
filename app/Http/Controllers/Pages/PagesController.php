<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Bundle\Bundle;
use App\Models\Course\Course;
use App\Models\Enterprise\Enterprise;
use App\Models\Faq\Faq;
use App\Models\User;

class PagesController extends Controller
{
    public function index()
    {
        seo()->setCanonical(url('/'));

        $courses = Course::latest()->available()->two(5)->website()->withCount(['lessons', 'chapters'])->get();
        $bpms = Course::latest()->available()->one(23)->website()->withCount(['lessons', 'chapters'])->get();
        $populars = Course::latest()->available()->featured()->website()->withCount(['lessons', 'chapters'])->get();
        $bundles = Bundle::available()->latest()->withCount('courses')->limit(4)->get();

        return view('pages.views.index')->with([
            'courses' => $courses,
            'bpms' => $bpms,
            'populars' => $populars,
            'bundles' => $bundles,
        ]);
    }

    public function coming()
    {
        seo()->setCanonical(url()->current());

        return view('pages.views.comings');
    }

    public function home()
    {
        return redirect()->route(User::auth()->redirect());
    }

    public function about()
    {
        seo()->setCanonical(url()->current());

        return view('pages.views.about')->with([
            'enterprises' => Enterprise::count(),
            'users' => User::count(),
        ]);
    }

    public function faqs()
    {
        seo()->setCanonical(url()->current());

        return view('pages.views.faqs')->with([
            'faqs' => Faq::get(),
        ]);
    }

    public function politics()
    {
        seo()->setCanonical(url()->current());

        return view('pages.views.politics');
    }

    public function terms()
    {
        seo()->setCanonical(url()->current());

        return view('pages.views.terms');
    }
}
