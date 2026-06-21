<?php

namespace App\Http\Controllers\Managers;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Str;

class DepartmentsController extends Controller
{
    public function index(Request $request)
    {

        $searchKey = $request->search;
        $available = $request->available;

        $departments = Department::descending();

        if ($searchKey != null) {
            $departments = $departments->where('code', 'like', '%'.$searchKey.'%');
        }

        if ($available != null) {
            $departments = $departments->where('available', $available);
        }

        $departments = $departments->paginate(paginationNumber());

        return view('managers.views.settings.departments.index')->with([
            'departments' => $departments,
            'available' => $available,
            'searchKey' => $searchKey,
        ]);

    }

    public function create()
    {

        $availables = $this->availableOptions();

        return view('managers.views.settings.departments.create')->with([
            'availables' => $availables,
        ]);

    }

    public function edit($slack)
    {

        $department = Department::slack($slack);

        $availables = $this->availableOptions();

        return view('managers.views.settings.departments.edit')->with([
            'department' => $department,
            'availables' => $availables,
        ]);

    }

    public function update(Request $request)
    {
        abort_unless(auth()->user()->can('departments.update'), 403);
        $department = Department::slack($request->slack);
        $department->title = Str::upper($request->title);
        $department->slug = Str::slug($request->title, '-');
        $department->available = $request->available;
        $department->update();

        return response()->json([
            'success' => true,
            'slack' => $department->slack,
            'message' => 'Se actualizo el paquete correctamente',
        ]);

    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('departments.create'), 403);

        $department = new Department;
        $department->slack = $this->generate_slack('departments');
        $department->title = Str::upper($request->title);
        $department->slug = Str::slug($request->title, '-');
        $department->available = $request->available;
        $department->save();

        return response()->json([
            'success' => true,
            'slack' => $department->slack,
            'message' => 'Se creado el paquete correctamente',
        ]);

    }

    public function destroy($slack)
    {
        abort_unless(auth()->user()->can('departments.delete'), 403);
        $department = Department::slack($slack);
        $department->delete();

        return redirect()->route('manager.departments');
    }
}
