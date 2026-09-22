<?php

namespace App\Http\Controllers\Supports\Enterprises;

use App\Exports\Supports\CoursesExport;
use App\Http\Controllers\Controller;
use App\Models\Course\Course;
use App\Models\Enterprise\Enterprise;
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

        // 1 query de agregación en vez de 3 counts sueltos.
        $agg = DB::table('enterprise_course')
            ->join('courses', 'courses.id', '=', 'enterprise_course.course_id')
            ->where('enterprise_course.enterprise_id', $enterprise->id)
            ->selectRaw(
                'COUNT(*) total,
                 SUM(courses.available = 1) `public`,
                 SUM(courses.available = 0) hidden'
            )->first();

        $stats = [
            'total' => (int) $agg->total,
            'public' => (int) $agg->public,
            'hidden' => (int) $agg->hidden,
        ];

        return view('supports.views.enterprises.courses.index')->with([
            'enterprise' => $enterprise,
            'courses' => $courses,
            'searchKey' => $searchKey,
            'stats' => $stats,
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
        // with('chapter'): la vista agrupa por chapter_id y muestra
        // $chapter->title por lección -- sin esto es una query extra por
        // lección del curso (N+1).
        $class = $course->lessons()->with('chapter')->get();

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
            // User::identification() aborta con 404 si no hay match -- eso hacía
            // que el "! $validate instanceof User" de abajo fuera código muerto
            // inalcanzable: una identificación con typo abortaba la matrícula
            // masiva COMPLETA con un 404 crudo a mitad de camino, dejando las
            // inscripciones ya creadas por iteraciones previas hechas y el
            // resto del lote sin procesar.
            $validate = User::where('identification', $user)->first();

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

        $requestedCourses = $request->courses ? explode(',', $request->courses) : [];
        // Sin Form Request aquí: se filtra contra ids de curso reales antes de
        // sync() (no había ninguna validación de existencia sobre estos ids).
        $newCourses = Course::query()->whereIn('id', $requestedCourses)->pluck('id')->all();

        if (! empty($newCourses)) {

            $toDetach = array_diff($currentCourses, $newCourses);

            // sync() en una sola operación atómica dentro de una transacción:
            // el detach()+attach() en loop suelto podía dejar a la empresa
            // con MENOS cursos que antes y ninguno nuevo si un attach() a
            // mitad de camino fallaba (mismo patrón ya corregido en
            // BundlesController::update()).
            DB::transaction(function () use ($enterprise, $newCourses) {
                $enterprise->courses()->sync($newCourses);
            });

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
