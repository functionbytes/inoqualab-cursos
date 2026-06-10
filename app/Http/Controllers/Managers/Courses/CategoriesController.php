<?php

namespace App\Http\Controllers\Managers\Courses;

use App\Http\Controllers\Controller;
use App\Models\Course\CourseCategorie;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoriesController extends Controller
{
    public function index(Request $request)
    {

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

        return view('managers.views.courses.categories.index')->with([
            'categories' => $categories,
            'available' => $available,
            'searchKey' => $searchKey,
        ]);
    }

    public function create()
    {

        $availables = $this->availableOptions();

        return view('managers.views.courses.categories.create')->with([
            'availables' => $availables,
        ]);

    }

    public function view($slug)
    {

        $categorie = CourseCategorie::slug($slug);

        return view('managers.views.courses.categories.view')->with([
            'categorie' => $categorie,
        ]);

    }

    public function edit($slack)
    {

        $categorie = CourseCategorie::slack($slack);

        $availables = $this->availableOptions();

        return view('managers.views.courses.categories.edit')->with([
            'categorie' => $categorie,
            'availables' => $availables,
        ]);

    }

    public function update(Request $request)
    {

        $categorie = CourseCategorie::slack($request->slack);
        $categorie->title = $request->title;
        $categorie->slug = Str::slug($request->title, '-');
        $categorie->available = $request->available;
        $categorie->update();

        return response()->json([
            'success' => true,
            'message' => 'Se actualizo la categoria correctamente',
        ]);

    }

    public function store(Request $request)
    {

        $categorie = new CourseCategorie;
        $categorie->slack = $this->generate_slack('courses');
        $categorie->title = $request->title;
        $categorie->slug = Str::slug($request->title, '-');
        $categorie->available = $request->available;
        $categorie->save();

        return response()->json([
            'success' => true,
            'message' => 'Se creo la categoria correctamente',
        ]);

    }

    public function destroy($slack)
    {

        $categorie = CourseCategorie::slack($slack);
        $categorie->delete();

        return redirect()->route('manager.categories.courses');
    }
}
