<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Instruction\Instruction;

class InstructionsController extends Controller
{
    public function index()
    {
        seo()->setTitle('Instructivos')->setCanonical(url()->current());

        return view('pages.views.instructions.index')->with([
            // Eager-load de la categoría: la tarjeta pinta $instruction->categorie->title.
            'instructions' => Instruction::available()->with('categorie')->get(),
        ]);
    }

    public function view($slug)
    {
        // Instruction no define scopeSlug (a diferencia de Course/Blog/Bundle):
        // el `Instruction::slug()` que había aquí lanzaba "Call to undefined
        // method" antes de llegar al fallback por slack, así que la página
        // respondía 500 en vez de resolver. Se busca por ambos en una consulta.
        //
        // Se filtra por available igual que index(): un instructivo despublicado
        // no debe seguir siendo accesible por URL directa.
        $instruction = Instruction::available()
            ->where(fn ($q) => $q->where('slug', $slug)->orWhere('slack', $slug))
            ->first();

        abort_unless($instruction instanceof Instruction, 404);

        seo()->loadFromModel($instruction->load('seoMeta'))
            ->setCanonical(url()->current());

        return view('pages.views.instructions.view')->with([
            'instruction' => $instruction->load('categorie'),
        ]);
    }
}
