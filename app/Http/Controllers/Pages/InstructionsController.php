<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Instruction\Instruction;

class InstructionsController extends Controller
{
    public function index()
    {
        seo()->setCanonical(url()->current());

        return view('pages.views.instructions.index')->with([
            'instructions' => Instruction::available()->get(),
        ]);
    }

    public function view($slug)
    {
        $instruction = Instruction::slug($slug);

        if (! $instruction instanceof Instruction) {
            $instruction = Instruction::slack($slug);
        }

        abort_unless($instruction instanceof Instruction, 404);

        seo()->loadFromModel($instruction->load('seoMeta'))
            ->setCanonical(url()->current());

        return view('pages.views.instructions.view')->with([
            'bundle' => $instruction,
        ]);
    }
}
