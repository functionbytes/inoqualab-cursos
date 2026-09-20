<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use App\Models\Instruction\Instruction;
use App\Models\Instruction\InstructionCategorie;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstructionsController extends Controller
{
    public function index(): View
    {

        // with('categorie'): index.blade.php usa $instruction->categorie->title
        // en el listado -- sin esto es una query extra por instrucción (N+1),
        // mismo fix que ya tiene filter() más abajo.
        $instructions = Instruction::available()->with('categorie')->get();
        // withCount: el contador junto a cada categoría en el panel lateral
        // salía recorriendo $instructions en la vista (otro N+1 en potencia
        // si esa colección crece) -- una sola query aquí lo resuelve.
        $categories = InstructionCategorie::available()->withCount('instructions')->get();

        return view('customers.views.instructions.index')->with([
            'instructions' => $instructions,
            'categories' => $categories,
        ]);

    }

    public function view($slack)
    {

        $instruction = Instruction::with('categorie')->slack($slack);
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

        // Devuelve el HTML ya renderizado (mismo patrón que quiz-result/
        // exam-result) en vez de JSON crudo -- antes el JS reconstruía las
        // tarjetas a mano con template strings, duplicando el markup/estilos
        // de la vista y quedando desincronizado con cualquier cambio acá.
        return view('customers.partials.views.instructions.list', [
            'instructions' => $instructions,
        ])->render();
    }
}
