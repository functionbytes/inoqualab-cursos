<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use App\Models\Instruction\Instruction;
use App\Models\Instruction\InstructionCategorie;
use Illuminate\Http\Request;

class InstructionsController extends Controller
{
    public function index()
    {

        $instructions = Instruction::available()->get();
        $categories = InstructionCategorie::available()->get();

        return view('customers.views.instructions.index')->with([
            'instructions' => $instructions,
            'categories' => $categories,
        ]);

    }

    public function view($slack)
    {

        $instruction = Instruction::slack($slack);
        abort_unless($instruction instanceof Instruction, 404);

        return view('customers.views.instructions.view')->with([
            'instruction' => $instruction,
        ]);

    }

    public function filter(Request $request)
    {
        if ($request->category_id == 'all') {
            // Si es 'all', retornar todas las instrucciones disponibles
            $instructions = Instruction::available()->with('categorie')->get();
        } else {
            // Si no es 'all', filtrar por la categoría seleccionada
            $instructions = Instruction::where('category_id', $request->category_id)
                ->with('categorie')
                ->available()
                ->get();
        }

        return response()->json($instructions);
    }
}
