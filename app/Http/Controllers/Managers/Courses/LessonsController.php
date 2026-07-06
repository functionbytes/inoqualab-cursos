<?php

namespace App\Http\Controllers\Managers\Courses;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Courses\StoreLessonRequest;
use App\Http\Requests\Managers\Courses\UpdateLessonRequest;
use App\Models\Course\Course;
use App\Models\Course\CourseLesson;
use App\Models\Course\CourseType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LessonsController extends Controller
{
    public function index(Request $request, $slack)
    {

        $course = Course::slack($slack);
        // Eager-load chapter/type: la vista pinta $lesson->chapter->title y
        // $lesson->type->title por fila (evita N+1: 35→7 queries por página).
        $lessons = CourseLesson::query()
            ->with(['chapter:id,title', 'type:id,title'])
            ->where('course_id', $course->id)
            ->orderBy('position');
        $types = CourseType::available()->orderBy('title')->get();
        $chapters = $course->chapters;
        $searchKey = $request->search;
        $available = $request->available;
        $chapter = $request->chapter;
        $type = $request->type;

        if ($searchKey) {
            $lessons = $lessons->where('title', 'like', '%'.$searchKey.'%');
        }

        if ($request->available != null) {
            $lessons = $lessons->where('available', $available);
        }

        if ($request->chapter != null) {
            $lessons = $lessons->where('chapter_id', $chapter);
        }

        if ($request->type != null) {
            $lessons = $lessons->where('type_id', $type);
        }

        $lessons = $lessons->paginate(paginationNumber());

        return view('managers.views.courses.lessons.index')->with([
            'course' => $course,
            'types' => $types,
            'lessons' => $lessons,
            'chapters' => $chapters,
            'chapter' => $chapter,
            'available' => $available,
            'searchKey' => $searchKey,
            // Opciones del modal de crear/editar clase. Distintas de $chapters/$types
            // de arriba, que son colecciones de modelos usadas por el modal de filtros.
            'availables' => $this->availableOptions(),
            'chapterOptions' => $chapters->pluck('title', 'id')->prepend('', ''),
            'typeOptions' => CourseType::latest()->get()->pluck('title', 'id')->prepend('', ''),
            'nextPosition' => count($course->lessons) + 1,
        ]);

    }

    /**
     * Datos de la clase para el modal de edición (fetch AJAX desde el listado).
     */
    public function edit($slack)
    {
        $lesson = CourseLesson::slack($slack);

        $mediaCollections = [
            2 => 'audio',
            3 => 'image',
            4 => 'zip',
            5 => 'pdf',
        ];

        $media = isset($mediaCollections[$lesson->type_id])
            ? $lesson->getFirstMedia($mediaCollections[$lesson->type_id])
            : null;

        return response()->json([
            'slack' => $lesson->slack,
            'title' => $lesson->title,
            'chapter_id' => $lesson->chapter_id,
            'type_id' => $lesson->type_id,
            'position' => $lesson->position,
            'available' => (int) $lesson->available,
            'detail' => $lesson->detail,
            'url' => $lesson->url,
            'platform' => $lesson->platform,
            // El tipo "video" guarda la duración en 'medition' (ver store()/update()).
            'duration' => (int) $lesson->type_id === 1 ? $lesson->medition : $lesson->duration,
            'size' => $lesson->size,
            'has_file' => (bool) $media,
            'file_name' => $media?->file_name,
        ]);
    }

    public function store(StoreLessonRequest $request)
    {
        abort_unless(auth()->user()->can('lessons.create'), 403);
        $course = Course::slack($request->course);

        if (! $course) {
            return response()->json(['success' => false, 'message' => 'Curso no encontrado.']);
        }

        // El capítulo debe pertenecer a este curso (evita inyectar un tema de otro curso).
        if (! in_array((int) $request->chapter, $course->chapters->pluck('id')->all(), true)) {
            return response()->json(['success' => false, 'message' => 'El tema seleccionado no pertenece a este curso.']);
        }

        $mediaCollections = [
            '2' => 'audio',
            '3' => 'image',
            '4' => 'zip',
            '5' => 'pdf',
        ];

        // Validar el enlace ANTES de escribir nada: hacerlo después del primer
        // save() dejaba una fila huérfana en BD cuando la URL no matcheaba.
        if ($request->type == '1') {
            $ytPattern = '/^(https?:\/\/)?(www\.)?(youtube\.com\/(watch\?v=|embed\/)|youtu\.be\/)[\w\-]+/';
            $vmPattern = '/^(https?:\/\/)?(www\.)?(vimeo\.com\/|player\.vimeo\.com\/video\/)[\d]+/';
            $platform = $request->platform;

            if ($platform === 'youtube' && ! preg_match($ytPattern, $request->url)) {
                return response()->json(['success' => false, 'message' => 'El enlace debe ser de YouTube.']);
            }
            if ($platform === 'vimeo' && ! preg_match($vmPattern, $request->url)) {
                return response()->json(['success' => false, 'message' => 'El enlace debe ser de Vimeo.']);
            }
            if (! $platform && ! preg_match($ytPattern, $request->url) && ! preg_match($vmPattern, $request->url)) {
                return response()->json(['success' => false, 'message' => 'El enlace debe ser de YouTube o Vimeo.']);
            }
        }

        DB::transaction(function () use ($request, $course, $mediaCollections) {
            $lesson = new CourseLesson;
            $lesson->course_id = $course->id;
            $lesson->slack = $this->generate_slack('course_lessons');
            $lesson->chapter_id = $request->chapter;
            $lesson->title = Str::upper($request->title);
            $lesson->medition = $request->medition;
            $lesson->position = $request->position;
            $lesson->detail = $request->detail;
            $lesson->available = $request->available;
            $lesson->type_id = $request->type;
            $lesson->save();

            if ($request->type == '1') {
                $lesson->url = $request->url;
                $lesson->platform = $request->platform;
                $lesson->medition = $request->duration;
            } elseif (isset($mediaCollections[$request->type]) && $request->hasFile('file')) {
                $collectionName = $mediaCollections[$request->type];
                $lesson->clearMediaCollection($collectionName);
                $media = $lesson->addMediaFromRequest('file')->toMediaCollection($collectionName);
                $lesson->size = round($media->size / 1048576, 2);
            } else {
                $lesson->clearMediaCollection();
            }

            $lesson->save();
        });

        return response()->json([
            'success' => true,
            'message' => 'Se creó la clase correctamente',
        ]);
    }

    public function update(UpdateLessonRequest $request)
    {
        abort_unless(auth()->user()->can('lessons.update'), 403);
        $lesson = CourseLesson::slack($request->slack);
        $previousType = (int) $lesson->type_id;

        $mediaCollections = [
            '2' => 'audio',
            '3' => 'image',
            '4' => 'zip',
            '5' => 'pdf',
        ];

        // Validación del enlace ANTES de escribir nada (ningún campo se ha
        // guardado todavía en este punto).
        if ($request->type == '1') {
            $url = $request->url;
            $platform = $request->platform;
            $ytPattern = '/^(https?:\/\/)?(www\.)?(youtube\.com\/(watch\?v=|embed\/)|youtu\.be\/)[\w\-]+/';
            $vmPattern = '/^(https?:\/\/)?(www\.)?(vimeo\.com\/|player\.vimeo\.com\/video\/)[\d]+/';

            if ($platform === 'youtube' && ! preg_match($ytPattern, $url)) {
                return response()->json(['success' => false, 'message' => 'El enlace debe ser de YouTube.']);
            }
            if ($platform === 'vimeo' && ! preg_match($vmPattern, $url)) {
                return response()->json(['success' => false, 'message' => 'El enlace debe ser de Vimeo.']);
            }
            if (! $platform && ! preg_match($ytPattern, $url) && ! preg_match($vmPattern, $url)) {
                return response()->json(['success' => false, 'message' => 'El enlace debe ser de YouTube o Vimeo.']);
            }
        }

        DB::transaction(function () use ($request, $lesson, $previousType, $mediaCollections) {
            $lesson->chapter_id = $request->chapter;
            $lesson->title = Str::upper($request->title);
            $lesson->position = $request->position;
            $lesson->detail = $request->detail;
            $lesson->available = $request->available;

            if ($request->type == '1') {
                if ($previousType !== 1 && isset($mediaCollections[$previousType])) {
                    $lesson->clearMediaCollection($mediaCollections[$previousType]);
                }

                $lesson->url = $request->url;
                $lesson->platform = $request->platform;
                $lesson->medition = $request->duration;
            } else {
                // Eliminar colección anterior si cambió el tipo
                if ((int) $request->type !== $previousType && isset($mediaCollections[$previousType])) {
                    $lesson->clearMediaCollection($mediaCollections[$previousType]);
                }

                if (isset($mediaCollections[$request->type]) && $request->hasFile('file')) {
                    $collectionName = $mediaCollections[$request->type];
                    $lesson->clearMediaCollection($collectionName);
                    $media = $lesson->addMediaFromRequest('file')->toMediaCollection($collectionName);
                    $lesson->size = round($media->size / 1048576, 2);
                }
            }

            $lesson->type_id = $request->type;
            $lesson->update();
        });

        return response()->json([
            'success' => true,
            'message' => 'Se actualizó la clase correctamente',
        ]);
    }

    public function destroy($slack)
    {
        abort_unless(auth()->user()->can('lessons.delete'), 403);

        $lesson = CourseLesson::slack($slack);
        $lesson->delete();

        return back();
    }

    public function bulkAction(Request $request): JsonResponse
    {
        $data = $request->validate([
            'action' => ['required', 'in:publish,hide,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:course_lessons,id'],
        ]);

        $permission = $data['action'] === 'delete' ? 'lessons.delete' : 'lessons.update';
        abort_unless(auth()->user()->can($permission), 403);

        $lessons = CourseLesson::whereIn('id', $data['ids'])->get();
        $count = $lessons->count();

        DB::transaction(function () use ($lessons, $data) {
            foreach ($lessons as $lesson) {
                match ($data['action']) {
                    'publish' => $lesson->update(['available' => 1]),
                    'hide' => $lesson->update(['available' => 0]),
                    // delete por modelo: soft-delete (preserva progreso) + limpia media.
                    'delete' => $lesson->delete(),
                };
            }
        });

        return response()->json(['success' => true, 'message' => "Acción aplicada sobre {$count} clase(s)."]);
    }

    /**
     * Reordena las lecciones visibles (drag&drop). Recibe los ids en su nuevo
     * orden y permuta entre ellos las posiciones que ya ocupaban, para no
     * alterar el resto de páginas.
     */
    public function reorder(Request $request): JsonResponse
    {
        abort_unless(auth()->user()->can('lessons.update'), 403);

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $first = CourseLesson::find($data['ids'][0]);
        if (! $first) {
            return response()->json(['success' => true, 'message' => 'Sin cambios.']);
        }

        // Secuencia global del curso (la página visible es un tramo contiguo).
        // Se coloca el nuevo sub-orden en los mismos slots y se renumera todo 1..N
        // (también auto-sana posiciones duplicadas heredadas).
        $all = CourseLesson::where('course_id', $first->course_id)
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
                CourseLesson::where('id', $id)->update(['position' => $index + 1]);
            }
        });

        return response()->json(['success' => true, 'message' => 'Orden actualizado.']);
    }
}
