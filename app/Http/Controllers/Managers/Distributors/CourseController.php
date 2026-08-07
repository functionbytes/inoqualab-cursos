<?php

namespace App\Http\Controllers\Managers\Distributors;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Distributors\UpdateDistributorCoursesRequest;
use App\Models\Course\Course;
use App\Models\Distributor\Distributor;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    public function index($slack)
    {

        $distributor = Distributor::slack($slack);

        $course = $distributor->courses;

        $courses = Course::available()->get();
        $courses = $courses->pluck('title', 'id');

        return view('managers.views.distributors.courses.index')->with([
            'distributor' => $distributor,
            'courses' => $courses,
            'course' => $course,
        ]);

    }

    public function update(UpdateDistributorCoursesRequest $request)
    {
        abort_unless(auth()->user()->can('distributors.update'), 403);

        $data = $request->validated();
        $distributor = Distributor::slack($data['slack']);

        $currentCourses = $distributor->courses->pluck('id')->toArray();

        $newCourses = $data['courses'];

        if (! empty($newCourses)) {

            $toDetach = array_diff($currentCourses, $newCourses);

            // sync() en una sola operación atómica dentro de una transacción:
            // el detach()+attach() en loop suelto podía dejar al distribuidor
            // con MENOS cursos que antes y ninguno nuevo si un attach() a
            // mitad de camino fallaba (mismo patrón ya corregido en
            // BundlesController::update()).
            DB::transaction(function () use ($distributor, $newCourses) {
                $distributor->courses()->sync($newCourses);
            });

            $response = [
                'success' => true,
                'message' => 'Cursos actualizados correctamente.',
                'detached_courses' => $toDetach,
                'attached_courses' => array_diff($newCourses, $currentCourses),
            ];

            return response()->json($response);
        }

        return response()->json([
            'success' => false,
            'message' => 'No se proporcionaron cursos para actualizar.',
        ]);

    }
}
