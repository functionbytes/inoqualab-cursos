<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Certifier;

class CertifiersController extends Controller
{
    public function index()
    {
        seo()->setTitle('Certificadores')->setCanonical(url()->current());

        $certifiers = Certifier::latest()->available()->get();

        return view('pages.views.certifiers.index')->with([
            'certifiers' => $certifiers,
        ]);
    }

    public function view($slack)
    {
        $certifier = Certifier::slack($slack);

        abort_unless($certifier instanceof Certifier, 404);

        $certifier->load(['seoMeta', 'courses']);

        seo()->loadFromModel($certifier)
            ->setCanonical(url()->current());

        $courses = $certifier->courses;

        return view('pages.views.certifiers.view')->with([
            'certifier' => $certifier,
            'courses' => $courses,
        ]);
    }
}
