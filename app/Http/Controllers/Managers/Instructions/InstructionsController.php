<?php

namespace App\Http\Controllers\Managers\Instructions;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Instructions\StoreInstructionRequest;
use App\Http\Requests\Managers\Instructions\UpdateInstructionRequest;
use App\Models\Instruction\Instruction;
use App\Models\Instruction\InstructionCategorie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InstructionsController extends Controller
{
    public function index(Request $request)
    {

        $searchKey = $request->search;
        $available = $request->available;

        $instructions = Instruction::descending()->with('categorie');

        if ($searchKey) {
            $instructions = $instructions->where('title', 'like', '%'.$searchKey.'%');
        }

        if ($request->available != null) {
            $instructions = $instructions->where('available', $available);
        }

        $instructions = $instructions->paginate(paginationNumber());

        return view('managers.views.settings.instructions.instructions.index')->with([
            'instructions' => $instructions,
            'available' => $available,
            'searchKey' => $searchKey,
        ]);
    }

    public function create()
    {

        $availables = $this->availableOptions();
        $categories = InstructionCategorie::latest()->available()->get();
        $categories->prepend('', '');
        $categories = $categories->pluck('title', 'id');

        return view('managers.views.settings.instructions.instructions.create')->with([
            'availables' => $availables,
            'categories' => $categories,
        ]);

    }

    public function edit($slack)
    {

        $instruction = Instruction::slack($slack);

        $availables = $this->availableOptions();

        $categories = InstructionCategorie::latest()->available()->get();
        $categories = $categories->pluck('title', 'id');

        return view('managers.views.settings.instructions.instructions.edit')->with([
            'availables' => $availables,
            'categories' => $categories,
            'instruction' => $instruction,
        ]);
    }

    public function store(StoreInstructionRequest $request)
    {
        abort_unless(auth()->user()->can('instructions.create'), 403);

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

    public function update(UpdateInstructionRequest $request)
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
        abort_unless(auth()->user()->can('instructions.delete'), 403);

        $instruction = Instruction::slack($slack);
        $instruction->delete();

        return redirect()->back();
    }

    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'action' => ['required', 'in:publish,hide,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:instructions,id'],
        ]);

        $permission = $request->action === 'delete' ? 'instructions.delete' : 'instructions.update';
        abort_unless(auth()->user()->can($permission), 403);

        $query = Instruction::whereIn('id', $request->ids);
        $count = $query->count();

        match ($request->action) {
            'publish' => $query->update(['available' => 1]),
            'hide' => $query->update(['available' => 0]),
            'delete' => $query->delete(),
        };

        return response()->json(['success' => true, 'message' => $count.' instrucción(es) procesadas.']);
    }
}
