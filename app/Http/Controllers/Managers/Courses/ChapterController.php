<?php

namespace App\Http\Controllers\Managers\Courses;

use App\Http\Controllers\Controller;
use App\Models\Course\Course;
use App\Models\Course\CourseChapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChapterController extends Controller
{
    public function index(Request $request, $slack)
    {

        $course = Course::slack($slack);
        $searchKey = $request->search;
        $available = $request->available;

        // orderBy position: la paginación sin ORDER BY es no-determinista y la
        // columna "Posición" quedaba cosmética.
        $chapters = CourseChapter::query()->where('course_id', $course->id)->orderBy('position');

        if ($searchKey) {
            $chapters = $chapters->where('title', 'like', '%'.$searchKey.'%');
        }

        if ($request->available != null) {
            $chapters = $chapters->where('available', $available);
        }

        $chapters = $chapters->paginate(paginationNumber());

        return view('managers.views.courses.chapters.index')->with([
            'course' => $course,
            'chapters' => $chapters,
            'available' => $available,
            'searchKey' => $searchKey,
            'availables' => $this->availableOptions(),
            'nextPosition' => count($course->chapters) + 1,
        ]);

    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('courses.create'), 403);

        $course = Course::slack($request->course);

        $chapter = new CourseChapter;
        $chapter->slack = $this->generate_slack('course_chapters');
        $chapter->title = Str::upper($request->title);
        $chapter->description = $request->description;
        $chapter->position = $request->position;
        $chapter->available = $request->available;
        $chapter->course_id = $course->id;
        $chapter->save();

        return response()->json([
            'success' => true,
            'message' => 'Se creo el tema correctamente',
        ]);

    }

    /**
     * Datos del tema para el modal de edición (fetch AJAX desde el listado).
     */
    public function edit($slack)
    {
        $chapter = CourseChapter::slack($slack);

        return response()->json([
            'slack' => $chapter->slack,
            'title' => $chapter->title,
            'description' => $chapter->description,
            'position' => $chapter->position,
            'available' => (int) $chapter->available,
        ]);
    }

    public function update(Request $request)
    {
        abort_unless(auth()->user()->can('courses.update'), 403);

        $chapter = CourseChapter::slack($request->slack);
        $chapter->title = Str::upper($request->title);
        $chapter->description = $request->description;
        $chapter->position = $request->position;
        $chapter->available = $request->available;
        $chapter->update();

        return response()->json([
            'success' => true,
            'message' => 'Se actualizo el tema correctamente',
        ]);

    }

    public function destroy($slack)
    {
        abort_unless(auth()->user()->can('courses.delete'), 403);

        $chapter = CourseChapter::slack($slack);
        $courseId = $chapter->course_id;

        DB::transaction(function () use ($chapter, $courseId) {
            // Borrado por modelo (no mass-delete): dispara los eventos Eloquent
            // para que Spatie MediaLibrary limpie los archivos/registros de media
            // de cada lección en vez de dejarlos huérfanos.
            $chapter->lessons()->get()->each->delete();
            $chapter->delete();

            // Renumera 1..N el resto de capítulos para no dejar huecos de posición.
            CourseChapter::where('course_id', $courseId)
                ->orderBy('position')->orderBy('id')->get(['id'])
                ->each(function ($remaining, $index) {
                    $remaining->update(['position' => $index + 1]);
                });
        });

        return back();

    }

    /**
     * Reordena los capítulos visibles (drag&drop): permuta entre los ids
     * recibidos las posiciones que ya ocupaban.
     */
    public function reorder(Request $request): JsonResponse
    {
        abort_unless(auth()->user()->can('courses.update'), 403);

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $first = CourseChapter::find($data['ids'][0]);
        if (! $first) {
            return response()->json(['success' => true, 'message' => 'Sin cambios.']);
        }

        // Renumera toda la secuencia de capítulos del curso 1..N aplicando el
        // nuevo sub-orden de la página visible (auto-sana duplicados).
        $all = CourseChapter::where('course_id', $first->course_id)
            ->orderBy('position')->orderBy('id')->pluck('id')->all();

        $slots = [];
        foreach ($data['ids'] as $id) {
            $idx = array_search($id, $all, true);
            if ($idx !== false) {
                $slots[] = $idx;
            }
        }
        sort($slots);
        foreach ($slots as $j => $slot) {
            $all[$slot] = $data['ids'][$j];
        }

        DB::transaction(function () use ($all) {
            foreach ($all as $index => $id) {
                CourseChapter::where('id', $id)->update(['position' => $index + 1]);
            }
        });

        return response()->json(['success' => true, 'message' => 'Orden actualizado.']);
    }
}
