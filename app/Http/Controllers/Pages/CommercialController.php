<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;

class CommercialController extends Controller
{
    public function index()
    {
        // La vista aún contiene contenido demo de la plantilla (sin contenido real del negocio):
        // se marca noindex y se excluye del sitemap hasta tener contenido definitivo.
        seo()->setTitle('Comercial')->setCanonical(url()->current())->noindex(true);

        return view('pages.views.commercials.index');
    }
}
