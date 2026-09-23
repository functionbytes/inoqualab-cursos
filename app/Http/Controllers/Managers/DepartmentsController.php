<?php

namespace App\Http\Controllers\Managers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Departments\BulkActionDepartmentRequest;
use App\Http\Requests\Managers\Departments\StoreDepartmentRequest;
use App\Http\Requests\Managers\Departments\UpdateDepartmentRequest;
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

    public function update(UpdateDepartmentRequest $request)
    {
        $department = Department::slack($request->slack);
        $department->title = Str::upper($request->title);
        $department->slug = Str::slug($request->title, '-');
        $department->available = $request->available;
        $department->update();

        return response()->json([
            'success' => true,
            'slack' => $department->slack,
            'message' => 'Se actualizó el departamento correctamente',
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
            'message' => 'Se creó el departamento correctamente',
        ]);

    }

    public function destroy($slack)
    {
        abort_unless(auth()->user()->can('departments.delete'), 403);
        $department = Department::slack($slack);
        $department->delete();

        return redirect()->route('manager.departments');
    }

    public function bulkAction(BulkActionDepartmentRequest $request): JsonResponse
    {
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
