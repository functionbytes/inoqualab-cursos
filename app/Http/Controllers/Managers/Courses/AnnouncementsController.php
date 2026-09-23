<?php

namespace App\Http\Controllers\Managers\Courses;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Courses\BulkActionAnnouncementRequest;
use App\Http\Requests\Managers\Courses\StoreAnnouncementRequest;
use App\Http\Requests\Managers\Courses\UpdateAnnouncementRequest;
use App\Models\Course\Course;
use App\Models\Course\CourseAnnouncement;
use Illuminate\Http\JsonResponse;
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

        $view = request()->ajax() ? 'managers.views.courses.announcements._table' : 'managers.views.courses.announcements.index';

        return view($view)->with([
            'course' => $course,
            'announcements' => $announcements,
            'available' => $available,
            'searchKey' => $searchKey,
            'availables' => $this->availableOptions(),
        ]);

    }

    public function store(StoreAnnouncementRequest $request)
    {
        $data = $request->validated();
        $course = Course::slack($data['course']);
        $announcement = new CourseAnnouncement;
        $announcement->slack = $this->generate_slack('course_announcements');
        $announcement->title = $data['title'];
        $announcement->description = $data['description'] ?? null;
        $announcement->course_id = $course->id;
        $announcement->user_id = auth()->id();
        $announcement->available = $data['available'] ?? null;
        $announcement->save();

        return response()->json([
            'success' => true,
            'message' => 'Se creó el anuncio correctamente',
        ]);

    }

    /**
     * Datos del anuncio para el modal de edición (fetch AJAX desde el listado).
     */
    public function edit($slack)
    {
        $announcement = CourseAnnouncement::slack($slack);

        return response()->json([
            'slack' => $announcement->slack,
            'title' => $announcement->title,
            'description' => $announcement->description,
            'available' => (int) $announcement->available,
        ]);
    }

    public function update(UpdateAnnouncementRequest $request)
    {
        $data = $request->validated();
        $announcement = CourseAnnouncement::slack($data['slack']);
        $announcement->title = $data['title'];
        $announcement->description = $data['description'] ?? null;
        $announcement->user_id = auth()->id();
        $announcement->available = $data['available'] ?? null;
        $announcement->update();

        return response()->json([
            'success' => true,
            'message' => 'Se actualizó el anuncio correctamente',
        ]);

    }

    public function destroy($slack)
    {
        abort_unless(auth()->user()->can('courses.delete'), 403);
        $announcement = CourseAnnouncement::slack($slack);
        $announcement->delete();

        return back();
    }

    public function bulkAction(BulkActionAnnouncementRequest $request): JsonResponse
    {
        $query = CourseAnnouncement::whereIn('id', $request->ids);
        $count = $query->count();

        match ($request->action) {
            'publish' => $query->update(['available' => 1]),
            'hide' => $query->update(['available' => 0]),
            'delete' => $query->delete(),
        };

        return response()->json(['success' => true, 'message' => $count.' anuncio(s) procesados.']);
    }
}
