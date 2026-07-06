<?php

namespace App\Http\Controllers\Managers\Enterprises;

use App\Http\Controllers\Controller;
use App\Models\Course\Course;
use App\Models\Enterprise\Enterprise;
use App\Services\InscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EnterprisesController extends Controller
{
    public function __construct(
        private readonly InscriptionService $inscriptionService
    ) {}

    public function index(Request $request)
    {

        $searchKey = $request->search;
        $available = $request->available;

        $enterprises = Enterprise::descending();

        if ($searchKey) {
            $enterprises = $enterprises->where('title', 'like', '%'.$searchKey.'%');
        }

        if ($request->available != null) {
            $enterprises = $enterprises->where('available', $available);
        }

        $enterprises = $enterprises->paginate(paginationNumber());

        return view('managers.views.enterprises.enterprises.index')->with([
            'enterprises' => $enterprises,
            'available' => $available,
            'searchKey' => $searchKey,
        ]);

    }

    public function create()
    {
        abort_unless(auth()->user()->can('enterprises.create'), 403);

        $availables = $this->availableOptions();

        return view('managers.views.enterprises.enterprises.create')->with([
            'availables' => $availables,
        ]);

    }

    public function edit($slack)
    {
        abort_unless(auth()->user()->can('enterprises.update'), 403);

        $enterprise = Enterprise::slack($slack);

        $availables = $this->availableOptions();

        return view('managers.views.enterprises.enterprises.edit')->with([
            'availables' => $availables,
            'enterprise' => $enterprise,
        ]);

    }

    public function update(Request $request)
    {
        abort_unless(auth()->user()->can('enterprises.update'), 403);
        $enterprise = Enterprise::slack($request->slack);

        if (Enterprise::where('email', $request->email)->where('id', '!=', $enterprise->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'El correo electrónico ya está registrado en nuestro sistema.']);
        }

        if (Enterprise::where('nit', $request->nit)->where('id', '!=', $enterprise->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'El NIT ya está registrado en nuestro sistema.']);
        }

        $enterprise->title = Str::upper($request->title);
        $enterprise->slug = Str::slug($request->title, '-');
        $enterprise->address = $request->address;
        $enterprise->cellphone = $request->cellphone;
        $enterprise->nit = $request->nit;
        $enterprise->email = $request->email;
        $enterprise->available = $request->available;
        $enterprise->save();

        return response()->json([
            'success' => true,
            'message' => 'Empresa actualizada correctamente.',
        ]);
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('enterprises.create'), 403);
        if (Enterprise::where('email', $request->email)->exists()) {
            return response()->json(['success' => false, 'message' => 'El correo electrónico ya está registrado en nuestro sistema.']);
        }

        if (Enterprise::where('nit', $request->nit)->exists()) {
            return response()->json(['success' => false, 'message' => 'El NIT ya está registrado en nuestro sistema.']);
        }

        $enterprise = new Enterprise;
        $enterprise->slack = $this->generate_slack('enterprises');
        $enterprise->title = Str::upper($request->title);
        $enterprise->slug = Str::slug($request->title, '-');
        $enterprise->address = $request->address;
        $enterprise->cellphone = $request->cellphone;
        $enterprise->nit = $request->nit;
        $enterprise->email = $request->email;
        $enterprise->available = 1;
        $enterprise->save();

        return response()->json([
            'success' => true,
            'message' => 'Empresa creada correctamente.',
        ]);
    }

    public function destroy($slack)
    {
        abort_unless(auth()->user()->can('enterprises.delete'), 403);

        $enterprise = Enterprise::slack($slack);
        $enterprise->delete();

        return redirect()->route('manager.enterprises');

    }

    public function navegation($slack)
    {
        abort_unless(auth()->user()->can('enterprises.view'), 403);

        $enterprise = Enterprise::slack($slack);

        return view('managers.views.enterprises.enterprises.navegation')->with([
            'enterprise' => $enterprise,
        ]);

    }

    public function inscriptions($slack)
    {
        abort_unless(auth()->user()->can('enterprises.view'), 403);

        $enterprise = Enterprise::slack($slack);
        $users = $enterprise->users()->available()->get();
        $courses = $enterprise->courses()->available()->get();
        $users = $users->pluck('identification', 'identification');
        $courses = $courses->pluck('title', 'id');

        return view('managers.views.enterprises.enterprises.inscription')->with([
            'enterprise' => $enterprise,
            'users' => $users,
            'courses' => $courses,
        ]);

    }

    public function generate(Request $request)
    {
        abort_unless(auth()->user()->can('enterprises.update'), 403);

        $enterprise = Enterprise::slack($request->enterprise);
        $course = Course::id($request->course);

        $identifications = array_filter(array_map('trim', explode(',', $request->users)));

        $this->inscriptionService->enrollSimpleBulk($identifications, $course, $enterprise);

        return response()->json([
            'success' => true,
            'message' => 'Se generado correctamente la empresa',
            'slack' => $enterprise->slack,
        ]);
    }
}
