<?php

namespace App\Http\Controllers\Managers\Courses;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Courses\BulkActionChapterRequest;
use App\Http\Requests\Managers\Courses\ReorderChapterRequest;
use App\Http\Requests\Managers\Courses\StoreChapterRequest;
use App\Http\Requests\Managers\Courses\UpdateChapterRequest;
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

        $view = request()->ajax() ? 'managers.views.courses.chapters._table' : 'managers.views.courses.chapters.index';

        return view($view)->with([
            'course' => $course,
            'chapters' => $chapters,
            'available' => $available,
            'searchKey' => $searchKey,
            'availables' => $this->availableOptions(),
            'nextPosition' => count($course->chapters) + 1,
        ]);

    }

    public function store(StoreChapterRequest $request)
    {
        $data = $request->validated();
        $course = Course::slack($data['course']);

        $chapter = new CourseChapter;
        $chapter->slack = $this->generate_slack('course_chapters');
        $chapter->title = Str::upper($data['title']);
        $chapter->description = $data['description'] ?? null;
        $chapter->position = $data['position'] ?? null;
        $chapter->available = $data['available'];
        $chapter->course_id = $course->id;
        $chapter->save();

        return response()->json([
            'success' => true,
            'message' => 'Se creó el tema correctamente',
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

    public function update(UpdateChapterRequest $request)
    {
        $data = $request->validated();
        $chapter = CourseChapter::slack($data['slack']);
        $chapter->title = Str::upper($data['title']);
        $chapter->description = $data['description'] ?? null;
        $chapter->position = $data['position'] ?? null;
        $chapter->available = $data['available'];
        $chapter->update();

        return response()->json([
            'success' => true,
            'message' => 'Se actualizó el tema correctamente',
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
    public function reorder(ReorderChapterRequest $request): JsonResponse
    {
        $data = $request->validated();

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

    public function bulkAction(BulkActionChapterRequest $request): JsonResponse
    {
        if ($request->action === 'delete') {
            $chapters = CourseChapter::whereIn('id', $request->ids)->get();
            $count = $chapters->count();
            $courseIds = $chapters->pluck('course_id')->unique();

            DB::transaction(function () use ($chapters, $courseIds) {
                foreach ($chapters as $chapter) {
                    // Borrado por modelo (no mass-delete): dispara los eventos Eloquent
                    // para que Spatie MediaLibrary limpie los archivos/registros de media
                    // de cada lección en vez de dejarlos huérfanos (mismo criterio que destroy()).
                    $chapter->lessons()->get()->each->delete();
                    $chapter->delete();
                }

                // Renumera 1..N el resto de capítulos de cada curso afectado.
                foreach ($courseIds as $courseId) {
                    CourseChapter::where('course_id', $courseId)
                        ->orderBy('position')->orderBy('id')->get(['id'])
                        ->each(function ($remaining, $index) {
                            $remaining->update(['position' => $index + 1]);
                        });
                }
            });

            return response()->json(['success' => true, 'message' => $count.' tema(s) procesados.']);
        }

        $query = CourseChapter::whereIn('id', $request->ids);
        $count = $query->count();

        match ($request->action) {
            'publish' => $query->update(['available' => 1]),
            'hide' => $query->update(['available' => 0]),
        };

        return response()->json(['success' => true, 'message' => $count.' tema(s) procesados.']);
    }
}
