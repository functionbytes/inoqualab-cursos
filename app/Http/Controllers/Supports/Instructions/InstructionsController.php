<?php

namespace App\Http\Controllers\Supports\Instructions;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Instructions\StoreInstructionRequest;
use App\Http\Requests\Managers\Instructions\UpdateInstructionRequest;
use App\Models\Instruction\Instruction;
use App\Models\Instruction\InstructionCategorie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InstructionsController extends Controller
{
    public function index(Request $request): View
    {

        $searchKey = $request->search;
        $available = $request->available;

        // with('categorie'): la vista muestra $instruction->categorie->title por
        // fila -- sin esto es una query extra por instrucción listada (N+1).
        $instructions = Instruction::with('categorie')->descending();

        if ($searchKey) {
            $instructions = $instructions->where('title', 'like', '%'.$searchKey.'%');
        }

        if ($request->available != null) {
            $instructions = $instructions->where('available', $available);
        }

        $instructions = $instructions->paginate(paginationNumber());

        // Total + desglose por estado en 1 sola query de agregación.
        $agg = Instruction::query()->selectRaw(
            'COUNT(*) total,
             SUM(available = 1) `public`,
             SUM(available = 0) hidden'
        )->first();

        $stats = [
            'total' => (int) $agg->total,
            'public' => (int) $agg->public,
            'hidden' => (int) $agg->hidden,
        ];

        return view('supports.views.instructions.instructions.index')->with([
            'instructions' => $instructions,
            'available' => $available,
            'searchKey' => $searchKey,
            'stats' => $stats,
        ]);
    }

    public function create(): View
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

    public function edit($slack): View
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

    public function store(StoreInstructionRequest $request): JsonResponse
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

    public function update(UpdateInstructionRequest $request): JsonResponse
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

    public function destroy($slack): RedirectResponse
    {

        $instruction = Instruction::slack($slack);
        $instruction->delete();

        return redirect()->back();
    }

    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'action' => ['required', 'in:delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:instructions,id'],
        ]);

        $query = Instruction::whereIn('id', $request->ids);
        $count = $query->count();

        match ($request->action) {
            'delete' => $query->delete(),
        };

        return response()->json(['success' => true, 'message' => $count.' instrucción(es) procesadas.']);
    }
}
