<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;

class CommercialController extends Controller
{
    public function index()
    {
        seo()->setCanonical(url()->current());

        return view('pages.views.commercials.index');
    }
}
