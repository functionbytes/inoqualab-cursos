<?php

namespace App\Http\Controllers\Supports\Instructions;

use App\Http\Controllers\Controller;
use App\Models\Instruction\Instruction;
use App\Models\Instruction\InstructionCategorie;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InstructionsController extends Controller
{
    public function index(Request $request)
    {

        $searchKey = $request->search;
        $available = $request->available;

        $instructions = Instruction::descending();

        if ($searchKey) {
            $instructions = $instructions->where('title', 'like', '%'.$searchKey.'%');
        }

        if ($request->available != null) {
            $instructions = $instructions->where('available', $available);
        }

        $instructions = $instructions->paginate(paginationNumber());

        return view('supports.views.instructions.instructions.index')->with([
            'instructions' => $instructions,
            'available' => $available,
            'searchKey' => $searchKey,
        ]);
    }

    public function create()
    {

        $availables = collect([
            ['id' => '1', 'label' => 'Publico'],
            ['id' => '0', 'label' => 'Oculto'],
        ]);

        $availables = $availables->pluck('label', 'id');
        $categories = InstructionCategorie::latest()->available()->get();
        $categories->prepend('', '');
        $categories = $categories->pluck('title', 'id');

        return view('supports.views.instructions.instructions.create')->with([
            'availables' => $availables,
            'categories' => $categories,
        ]);

    }

    public function edit($slack)
    {

        $instruction = Instruction::slack($slack);

        $availables = collect([
            ['id' => '1', 'label' => 'Publico'],
            ['id' => '0', 'label' => 'Oculto'],
        ]);

        $availables = $availables->pluck('label', 'id');

        $categories = InstructionCategorie::latest()->available()->get();
        $categories = $categories->pluck('title', 'id');

        return view('supports.views.instructions.instructions.edit')->with([
            'availables' => $availables,
            'categories' => $categories,
            'instruction' => $instruction,
        ]);
    }

    public function store(Request $request)
    {

        $instruction = new Instruction;
        $instruction->slack = $this->generate_slack('instructions');
        $instruction->title = $request->title;
        $instruction->short = $request->short;
        $instruction->description = $request->description;
        $instruction->slug = Str::slug($request->title, '-');
        $instruction->available = $request->available;
        $instruction->category_id = $request->categorie;
        $instruction->tags = $request->tags;
        $instruction->save();

        return response()->json([
            'success' => true,
            'slack' => $instruction->slack,
            'message' => 'Se creo la instrucción correctamente',
        ]);

    }

    public function update(Request $request)
    {

        $instruction = Instruction::slack($request->slack);
        $instruction->title = $request->title;
        $instruction->description = $request->description;
        $instruction->short = $request->short;
        $instruction->slug = Str::slug($request->title, '-');
        $instruction->available = $request->available;
        $instruction->category_id = $request->categorie;
        $instruction->tags = $request->tags;
        $instruction->update();

        return response()->json([
            'success' => true,
            'slack' => $instruction->slack,
            'message' => 'Se actualizado la instrucción correctamente',
        ]);

    }

    public function destroy($slack)
    {

        $instruction = Instruction::slack($slack);
        $instruction->delete();

        return redirect()->back();
    }
}
