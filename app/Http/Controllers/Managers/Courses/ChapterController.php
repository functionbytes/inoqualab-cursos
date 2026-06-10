<?php

namespace App\Http\Controllers\Managers\Courses;

use App\Http\Controllers\Controller;
use App\Models\Course\Course;
use App\Models\Course\CourseChapter;
use App\Models\Course\CourseLesson;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChapterController extends Controller
{
    public function index(Request $request, $slack)
    {

        $course = Course::slack($slack);
        $searchKey = $request->search;
        $available = $request->available;

        $chapters = CourseChapter::query()->where('course_id', $course->id);

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
        ]);

    }

    public function store(Request $request)
    {

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

    public function create($slack)
    {

        $course = Course::slack($slack);
        $chapters = $course->chapters;
        $position = count($chapters) + 1;

        $availables = $this->availableOptions();

        return view('managers.views.courses.chapters.create')->with([
            'course' => $course,
            'position' => $position,
            'availables' => $availables,
        ]);

    }

    public function edit($slack)
    {

        $chapter = CourseChapter::slack($slack);
        $course = $chapter->course;

        $availables = $this->availableOptions();

        return view('managers.views.courses.chapters.edit')->with([
            'course' => $course,
            'chapter' => $chapter,
            'availables' => $availables,
        ]);

    }

    public function update(Request $request)
    {

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

        $chapter = CourseChapter::slack($slack);
        CourseLesson::where('chapter_id', $chapter->id)->delete();
        $chapter->delete();

        return back();

    }
}
