<?php

namespace App\Http\Controllers\Managers\Courses;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Courses\BulkActionCourseCategoryRequest;
use App\Http\Requests\Managers\Courses\StoreCourseCategoryRequest;
use App\Http\Requests\Managers\Courses\UpdateCourseCategoryRequest;
use App\Models\Course\CourseCategorie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoriesController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('courses.view'), 403);

        $searchKey = $request->search;
        $available = $request->available;

        $categories = CourseCategorie::descending();

        if ($searchKey) {
            $categories = $categories->where('title', 'like', '%'.$searchKey.'%');
        }

        if ($request->available != null) {
            $categories = $categories->where('available', $available);
        }

        $categories = $categories->paginate(paginationNumber());

        $view = request()->ajax() ? 'managers.views.courses.categories._table' : 'managers.views.courses.categories.index';

        return view($view)->with([
            'categories' => $categories,
            'available' => $available,
            'searchKey' => $searchKey,
        ]);
    }

    public function create()
    {
        abort_unless(auth()->user()->can('courses.view'), 403);

        $availables = $this->availableOptions();

        return view('managers.views.courses.categories.create')->with([
            'availables' => $availables,
        ]);

    }

    public function view($slug)
    {
        abort_unless(auth()->user()->can('courses.view'), 403);

        $categorie = CourseCategorie::slug($slug);

        return view('managers.views.courses.categories.view')->with([
            'categorie' => $categorie,
        ]);

    }

    public function edit($slack)
    {
        abort_unless(auth()->user()->can('courses.view'), 403);

        $categorie = CourseCategorie::slack($slack);

        $availables = $this->availableOptions();

        return view('managers.views.courses.categories.edit')->with([
            'categorie' => $categorie,
            'availables' => $availables,
        ]);

    }

    public function update(UpdateCourseCategoryRequest $request)
    {
        abort_unless(auth()->user()->can('courses.update'), 403);

        $categorie = CourseCategorie::slack($request->slack);
        $categorie->title = $request->title;
        $categorie->slug = Str::slug($request->title, '-');
        $categorie->available = $request->available;
        $categorie->update();

        return response()->json([
            'success' => true,
            'message' => 'Se actualizó la categoría correctamente',
        ]);

    }

    public function store(StoreCourseCategoryRequest $request)
    {
        abort_unless(auth()->user()->can('courses.create'), 403);

        $categorie = new CourseCategorie;
        $categorie->slack = $this->generate_slack('courses');
        $categorie->title = $request->title;
        $categorie->slug = Str::slug($request->title, '-');
        $categorie->available = $request->available;
        $categorie->save();

        return response()->json([
            'success' => true,
            'message' => 'Se creó la categoría correctamente',
        ]);

    }

    public function destroy($slack)
    {
        abort_unless(auth()->user()->can('courses.delete'), 403);

        $categorie = CourseCategorie::slack($slack);

        // Una categoría con cursos asociados no puede eliminarse: dejaría cursos huérfanos
        // y rompería los listados que muestran $course->categorie->title.
        if ($categorie->courses()->exists()) {
            return redirect()->route('manager.categories.courses')
                ->with('error', 'No se puede eliminar: la categoría tiene cursos asociados. Reasigna esos cursos primero.');
        }

        $categorie->delete();

        return redirect()->route('manager.categories.courses');
    }

    public function bulkAction(BulkActionCourseCategoryRequest $request): JsonResponse
    {
        if ($request->action === 'delete') {
            // Mismo resguardo que destroy(): una categoría con cursos asociados
            // no se elimina (dejaría cursos huérfanos); se omite del conteo.
            $count = CourseCategorie::whereIn('id', $request->ids)
                ->whereDoesntHave('courses')
                ->delete();

            return response()->json(['success' => true, 'message' => $count.' categoria(s) procesados.']);
        }

        $query = CourseCategorie::whereIn('id', $request->ids);
        $count = $query->count();

        match ($request->action) {
            'publish' => $query->update(['available' => 1]),
            'hide' => $query->update(['available' => 0]),
        };

        return response()->json(['success' => true, 'message' => $count.' categoria(s) procesados.']);
    }
}
