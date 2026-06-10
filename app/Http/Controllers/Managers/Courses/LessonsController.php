<?php

namespace App\Http\Controllers\Managers\Courses;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Courses\StoreLessonRequest;
use App\Models\Course\Course;
use App\Models\Course\CourseLesson;
use App\Models\Course\CourseType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LessonsController extends Controller
{
    public function index(Request $request, $slack)
    {

        $course = Course::slack($slack);
        $lessons = CourseLesson::query()->where('course_id', $course->id);
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
        ]);

    }

    public function create($slack)
    {

        $course = Course::slack($slack);
        $lessons = $course->lessons;

        $position = count($lessons) + 1;
        $chapters = $course->chapters;
        $chapters = $chapters->prepend('', '');
        $chapters = $chapters->pluck('title', 'id');

        $types = CourseType::latest()->get();
        $types = $types->prepend('', '');
        $types = $types->pluck('title', 'id');

        $availables = $this->availableOptions();

        return view('managers.views.courses.lessons.create')->with([
            'course' => $course,
            'types' => $types,
            'chapters' => $chapters,
            'availables' => $availables,
            'position' => $position,
        ]);

    }

    public function edit($slack)
    {

        $lesson = CourseLesson::slack($slack);
        $course = $lesson->course;
        $chapters = $course->chapters;

        $chapters = $chapters->prepend('', '');
        $chapters = $chapters->pluck('title', 'id');

        $types = CourseType::latest()->get();
        $types = $types->prepend('', '');
        $types = $types->pluck('title', 'id');

        $availables = $this->availableOptions();

        $mediaCollections = [
            '2' => 'audio',
            '3' => 'image',
            '4' => 'zip',
            '5' => 'pdf',
        ];

        $link = null;
        $file = 'false';

        if ($lesson->type_id == 1) {
            $link = null;
            $file = 'false';
        } elseif ($lesson->type_id == 2) {
            $audio = $lesson->getfirstMedia('audio');
            $link = $audio ? $audio->file_name : null;
            $file = $lesson->getMedia('audio')->count() > 0 ? 'true' : 'false';
        } elseif ($lesson->type_id == 3) {
            $image = $lesson->getfirstMedia('image');
            $link = $image ? $image->file_name : null;
            $file = $lesson->getMedia('image')->count() > 0 ? 'true' : 'false';
        } elseif ($lesson->type_id == 4) {
            $zip = $lesson->getfirstMedia('zip');
            $link = $zip ? $zip->file_name : null;
            $file = $lesson->getMedia('zip')->count() > 0 ? 'true' : 'false';
        } elseif ($lesson->type_id == 5) {
            $pdf = $lesson->getfirstMedia('pdf');
            $link = $pdf ? $pdf->file_name : null;
            $file = $lesson->getMedia('pdf')->count() > 0 ? 'true' : 'false';
        } else {
            $link = null;
            $file = 'false';
        }

        return view('managers.views.courses.lessons.edit')->with([
            'course' => $course,
            'lesson' => $lesson,
            'types' => $types,
            'availables' => $availables,
            'chapters' => $chapters,
            'link' => $link,
            'file' => $file,
        ]);

    }

    public function store(StoreLessonRequest $request)
    {
        $course = Course::slack($request->course);

        if (! $course) {
            return response()->json(['success' => false, 'message' => 'Curso no encontrado.']);
        }

        // El capítulo debe pertenecer a este curso (evita inyectar un tema de otro curso).
        if (! in_array((int) $request->chapter, $course->chapters->pluck('id')->all(), true)) {
            return response()->json(['success' => false, 'message' => 'El tema seleccionado no pertenece a este curso.']);
        }

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

        $mediaCollections = [
            '2' => 'audio',
            '3' => 'image',
            '4' => 'zip',
            '5' => 'pdf',
        ];

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

            $lesson->url = $request->url;
            $lesson->platform = $platform;
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

        return response()->json([
            'success' => true,
            'message' => 'Se creó la clase correctamente',
        ]);
    }

    public function update(Request $request)
    {
        $lesson = CourseLesson::slack($request->slack);
        $lesson->chapter_id = $request->chapter;
        $lesson->title = Str::upper($request->title);
        $lesson->position = $request->position;
        $lesson->detail = $request->detail;
        $lesson->available = $request->available;
        $previousType = (int) $lesson->type_id;

        $mediaCollections = [
            '2' => 'audio',
            '3' => 'image',
            '4' => 'zip',
            '5' => 'pdf',
        ];

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

            if ($previousType !== 1 && isset($mediaCollections[$previousType])) {
                $lesson->clearMediaCollection($mediaCollections[$previousType]);
            }

            $lesson->url = $url;
            $lesson->platform = $platform;
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

        return response()->json([
            'success' => true,
            'message' => 'Se actualizó la clase correctamente',
        ]);
    }

    public function destroy($slack)
    {

        $lesson = CourseLesson::slack($slack);
        $lesson->delete();

        return back();

        // if ($courseclass->type == "video") {

        //     $video_file = @file_get_contents(public_path() . '/video/class/' . $courseclass->video);

        //     if ($video_file) {
        //         unlink(public_path() . '/video/class/' . $courseclass->video);
        //     }
        // }

        // if ($courseclass->type == "audio") {

        //     $video_file = @file_get_contents(public_path() . '/files/audio/' . $courseclass->audio);

        //     if ($video_file) {
        //         unlink(public_path() . '/files/audio/' . $courseclass->audio);
        //     }
        // }

        // if ($courseclass->type == "image") {

        //     $image_file = @file_get_contents(public_path() . '/images/class/' . $courseclass->image);

        //     if ($image_file) {
        //         unlink(public_path() . '/images/class/' . $courseclass->image);
        //     }
        // }

        // if ($courseclass->type == "zip") {

        //     $zip_file = @file_get_contents(public_path() . '/files/zip/' . $courseclass->zip);

        //     if ($zip_file) {
        //         unlink(public_path() . '/files/zip/' . $courseclass->zip);
        //     }
        // }

        // if ($courseclass->type == "pdf") {

        //     $pdf_file = @file_get_contents(public_path() . '/files/pdf/' . $courseclass->pdf);

        //     if ($pdf_file) {
        //         unlink(public_path() . '/files/pdf/' . $courseclass->pdf);
        //     }
        // }

        // if ($courseclass->preview_type = "video") {
        //     $content = @file_get_contents(public_path() . '/video/class/preview/' . $courseclass->preview_video);
        //     if ($content) {
        //         unlink(public_path() . '/video/class/preview/' . $courseclass->preview_video);
        //     }
        // }

    }
}
