<?php

namespace App\Http\Controllers\Supports\Enterprises;

use App\Exports\Supports\CoursesExport;
use App\Http\Controllers\Controller;
use App\Models\Course\Course;
use App\Models\Enterprise\Enterprise;
use App\Models\Enterprise\EnterpriseCourse;
use App\Models\Inscription;
use App\Models\User;
use App\Services\InscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class CourseController extends Controller
{
    public function index(Request $request, $slack)
    {

        $searchKey = $request->search;
        $enterprise = Enterprise::slack($slack);

        $courses = $enterprise->courses();

        if ($searchKey != null) {
            $courses = $courses->where('title', 'like', '%'.$searchKey.'%');
        }

        $courses = $courses->paginate(paginationNumber());

        return view('supports.views.enterprises.courses.index')->with([
            'enterprise' => $enterprise,
            'courses' => $courses,
            'searchKey' => $searchKey,
        ]);

    }

    public function view(Request $request, $enterprise, $course)
    {

        $searchKey = $request->search;
        $year = $request->year;
        $culminated = $request->culminated;
        $enterprise = Enterprise::slack($enterprise);
        $course = Course::slack($course);

        $baseQuery = fn () => User::query()
            ->join('enterprise_user', fn ($j) => $j->on('users.id', '=', 'enterprise_user.user_id'))
            ->where('enterprise_user.enterprise_id', $enterprise->id)
            ->join('inscriptions', fn ($j) => $j->on('users.id', '=', 'inscriptions.user_id'))
            ->join('orders', fn ($j) => $j->on('orders.id', '=', 'inscriptions.order_id'))
            ->where('inscriptions.course_id', $course->id);

        $inscriptions = $baseQuery()->select(
            'users.slack',
            'users.firstname',
            'users.lastname',
            'users.available',
            'users.identification',
            'inscriptions.id',
            'inscriptions.slack as slack',
            'inscriptions.percent',
            'inscriptions.order_id',
            'inscriptions.enroll_start',
            'inscriptions.enroll_expire',
            'inscriptions.enroll_culminated',
            'inscriptions.culminated',
            'inscriptions.created_at',
            'inscriptions.updated_at',
        )->orderBy('enroll_culminated', 'desc');

        $years = $baseQuery()
            ->selectRaw('YEAR(enroll_culminated) as year')
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->pluck('year');

        if ($searchKey) {
            $inscriptions = $inscriptions->where(function ($query) use ($searchKey) {
                $query->where('users.firstname', 'like', '%'.$searchKey.'%')
                    ->orWhere('users.lastname', 'like', '%'.$searchKey.'%')
                    ->orWhere(DB::raw("CONCAT(users.firstname, ' ', users.lastname)"), 'like', '%'.$searchKey.'%')
                    ->orWhere('users.email', $searchKey)
                    ->orWhere('users.identification', $searchKey);
            });
        }

        if ($year != null) {
            $inscriptions = $inscriptions->whereYear('inscriptions.enroll_culminated', $year);
        }

        if ($culminated != null) {
            $inscriptions = $inscriptions->where('inscriptions.culminated', $culminated);
        }

        $inscriptions = $inscriptions->paginate(paginationNumber());

        return view('supports.views.enterprises.courses.view')->with([
            'course' => $course,
            'culminated' => $culminated,
            'inscriptions' => $inscriptions,
            'count' => $inscriptions,
            'enterprise' => $enterprise,
            'year' => $year,
            'years' => $years,
            'searchKey' => $searchKey,
        ]);

    }

    public function progress($slack)
    {

        $inscription = Inscription::slack($slack);
        $progress = $inscription->progress;
        $user = $inscription->user;
        $course = $inscription->course;
        $class = $course->lessons;

        return view('supports.views.enterprises.courses.progress')->with([
            'user' => $user,
            'course' => $course,
            'progress' => $progress,
            'class' => $class,
            'inscription' => $inscription,
        ]);

    }

    public function reasign($enterprise, $course)
    {

        $enterprise = Enterprise::slack($enterprise);
        $courses = $enterprise->courses;
        $course = Course::slack($course);

        $alls = $enterprise->users;
        $users = $alls->pluck('identification', 'identification');
        $users->prepend('Todos', '0');

        $collections = new Collection;

        foreach ($courses as $item) {
            if ($item->id != $course->id) {
                $collections->prepend($item);
            }
        }

        $collections->prepend('', '');
        $courses = $collections->pluck('title', 'id');

        return view('supports.views.enterprises.courses.reasign')->with([
            'courses' => $courses,
            'users' => $users,
            'enterprise' => $enterprise,
            'course' => $course,
        ]);

    }

    public function includes(Request $request, InscriptionService $inscriptions)
    {

        $enterprise = Enterprise::slack($request->enterprise);
        abort_unless($enterprise instanceof Enterprise, 404);
        $course = Course::slack($request->course);
        abort_unless($enterprise->courses()->where('courses.id', $course->id)->exists(), 404);
        $users = explode(',', $request->users);

        foreach ($users as $user) {
            $validate = User::identification($user);

            if (! $validate instanceof User) {
                continue;
            }

            // Solo se matricula a usuarios que pertenecen a la empresa.
            if (! $enterprise->users()->where('users.id', $validate->id)->exists()) {
                continue;
            }

            $inscriptions->enrollSimple($validate, $course);
        }

        return response()->json($enterprise->slack);
    }

    public function report($enterprise, $course)
    {

        $enterprise = Enterprise::slack($enterprise);
        $course = Course::slack($course);

        $modalities = collect([
            ['id' => '0', 'title' => 'Todos'],
            ['id' => '1', 'title' => 'Culminado'],
            ['id' => '2', 'title' => 'Pendiente'],
        ]);

        $modalities = $modalities->pluck('title', 'id');

        return view('supports.views.enterprises.courses.reports')->with([
            'modalities' => $modalities,
            'enterprise' => $enterprise,
            'course' => $course,
        ]);
    }

    public function generate(Request $request)
    {
        $enterprise = $request->enterprise;
        $course = $request->course;
        $modalitie = $request->modalitie;

        return Excel::download(new CoursesExport($course, $enterprise, $modalitie), 'REPORTE CURSO '.date('Y-m-d').'.xlsx');
    }

    public function destroy($enterprise, $course)
    {

        $enterprise = Enterprise::slack($enterprise);
        $course = Course::slack($course);

        $inscription = EnterpriseCourse::validate($enterprise->id, $course->id);
        abort_if($inscription === null, 404);
        $inscription->delete();

        return redirect()->route('support.supports.courses', $enterprise->slack);
    }

    public function destroyInscription($slack)
    {
        $inscription = Inscription::slack($slack);
        $inscription->delete();

        return back();
    }

    public function details($slack)
    {

        $inscription = Inscription::slack($slack);
        $progress = $inscription->progress;
        $user = $inscription->user;
        $course = $inscription->course;
        $class = $course->lessons;

        return view('supports.views.enterprises.courses.details')->with([
            'user' => $user,
            'course' => $course,
            'class' => $class,
            'inscription' => $inscription,
            'progress' => $progress,
        ]);

    }

    public function assign($slack)
    {

        $enterprise = Enterprise::slack($slack);
        $distributor = $enterprise->distributor;

        // 143 de 252 empresas no tienen distribuidor asignado: sin catálogo de
        // distribuidor del cual elegir, se ofrece el catálogo completo de cursos.
        $courses = $distributor ? $distributor->courses : Course::available()->get();
        $course = $enterprise->courses;

        $courses = $courses->pluck('title', 'id');

        return view('supports.views.enterprises.courses.assign')->with([
            'enterprise' => $enterprise,
            'courses' => $courses,
            'course' => $course,
        ]);

    }

    public function update(Request $request)
    {

        $enterprise = Enterprise::slack($request->slack);

        $currentCourses = $enterprise->courses->pluck('id')->toArray();

        $newCourses = $request->courses ? explode(',', $request->courses) : [];

        if (! empty($newCourses)) {

            $toDetach = array_diff($currentCourses, $newCourses);
            $enterprise->courses()->detach($toDetach);

            foreach ($newCourses as $id) {
                if (! in_array($id, $currentCourses)) {
                    $enterprise->courses()->attach($id);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Cursos actualizados correctamente.',
                'detached_courses' => $toDetach,
                'attached_courses' => array_diff($newCourses, $currentCourses),
            ]);

        }

        return response()->json([
            'success' => false,
            'message' => 'No se proporcionaron cursos para actualizar.',
        ]);

    }
}
