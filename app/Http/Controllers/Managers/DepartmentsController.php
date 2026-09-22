<?php

namespace App\Http\Controllers\Managers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Departments\StoreDepartmentRequest;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
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

        $view = request()->ajax() ? 'managers.views.settings.departments._table' : 'managers.views.settings.departments.index';

        return view($view)->with([
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

    public function store(StoreDepartmentRequest $request)
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

    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'action' => ['required', 'in:activate,deactivate,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:departments,id'],
        ]);

        $permission = $request->action === 'delete' ? 'departments.delete' : 'departments.update';
        abort_unless(auth()->user()->can($permission), 403);

        $query = Department::whereIn('id', $request->ids);
        $count = $query->count();

        match ($request->action) {
            'activate' => $query->update(['available' => 1]),
            'deactivate' => $query->update(['available' => 0]),
            'delete' => $query->delete(),
        };

        return response()->json(['success' => true, 'message' => $count.' departamento(s) procesados.']);
    }
}
