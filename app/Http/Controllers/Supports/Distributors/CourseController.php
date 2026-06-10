<?php

namespace App\Http\Controllers\Supports\Distributors;

use App\Http\Controllers\Controller;
use App\Models\Course\Course;
use App\Models\Distributor\Distributor;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index($slack)
    {

        $distributor = Distributor::slack($slack);

        $course = $distributor->courses;

        $courses = Course::available()->get();
        $courses = $courses->pluck('title', 'id');

        return view('supports.views.distributors.courses.index')->with([
            'distributor' => $distributor,
            'courses' => $courses,
            'course' => $course,
        ]);

    }

    public function update(Request $request)
    {

        $distributor = Distributor::slack($request->slack);

        if (! $distributor) {
            return response()->json([
                'success' => false,
                'message' => 'Distribuidor no encontrado.',
            ], 404);
        }

        $currentCourses = $distributor->courses->pluck('id')->toArray();

        $newCourses = $request->courses ? explode(',', $request->courses) : [];

        if (! empty($newCourses)) {

            $toDetach = array_diff($currentCourses, $newCourses);
            $distributor->courses()->detach($toDetach);

            foreach ($newCourses as $id) {
                if (! in_array($id, $currentCourses)) {
                    $distributor->courses()->attach($id);
                }
            }

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
