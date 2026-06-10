<?php

namespace App\Http\Controllers\Managers\Courses;

use App\Http\Controllers\Controller;
use App\Models\Course\Course;
use App\Models\Course\CourseAnnouncement;
use Illuminate\Http\Request;

class AnnouncementsController extends Controller
{
    public function index(Request $request, $slack)
    {

        $course = Course::slack($slack);
        $searchKey = $request->search;
        $available = $request->available;

        $announcements = CourseAnnouncement::descending()->where('course_id', $course->id);

        if ($searchKey) {
            $announcements = $announcements->where('title', 'like', '%'.$searchKey.'%');
        }

        if ($request->available != null) {
            $announcements = $announcements->where('available', $available);
        }

        $announcements = $announcements->paginate(paginationNumber());

        return view('managers.views.courses.announcements.index')->with([
            'course' => $course,
            'announcements' => $announcements,
            'available' => $available,
            'searchKey' => $searchKey,
        ]);

    }

    public function create($slack)
    {

        $course = Course::slack($slack);

        $availables = $this->availableOptions();

        return view('managers.views.courses.announcements.create')->with([
            'course' => $course,
            'availables' => $availables,
        ]);

    }

    public function store(Request $request)
    {

        $course = Course::slack($request->course);
        $announcement = new CourseAnnouncement;
        $announcement->slack = $this->generate_slack('course_announcements');
        $announcement->title = $request->title;
        $announcement->description = $request->description;
        $announcement->course_id = $course->id;
        $announcement->user_id = auth()->id();
        $announcement->available = $request->available;
        $announcement->save();

        return response()->json([
            'success' => true,
            'message' => 'Se creado la anuncio correctamente',
        ]);

    }

    public function edit($slack)
    {

        $announcement = CourseAnnouncement::slack($slack);
        $course = $announcement->course;

        $availables = $this->availableOptions();

        return view('managers.views.courses.announcements.edit')->with([
            'course' => $course,
            'announcement' => $announcement,
            'availables' => $availables,
        ]);

    }

    public function update(Request $request)
    {

        $announcement = CourseAnnouncement::slack($request->slack);
        $announcement->title = $request->title;
        $announcement->description = $request->description;
        $announcement->user_id = auth()->id();
        $announcement->available = $request->available;
        $announcement->update();

        return response()->json([
            'success' => true,
            'message' => 'Se actualizo el anuncio correctamente',
        ]);

    }

    public function destroy($slack)
    {
        $announcement = CourseAnnouncement::slack($slack);
        $announcement->delete();

        return back();
    }
}
