<?php

namespace App\Http\Controllers\Distributors\Enterprises;

use App\Exports\Distributors\CoursesExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Distributors\Enterprises\BulkActionEnterpriseCourseRequest;
use App\Models\Course\Course;
use App\Models\Enterprise\Enterprise;
use App\Models\Enterprise\EnterpriseCourse;
use App\Models\Inscription;
use App\Models\User;
use App\Services\InscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class CourseController extends Controller
{
    /** Empresa que pertenece al distribuidor autenticado, o 404 (evita IDOR). */
    private function managedEnterprise(?string $slack): Enterprise
    {
        // El slack llega de un POST sin validar: sin este guard, pasar
        // null a un parámetro `string` lanzaba TypeError y devolvía un
        // 500 en vez de un 404 limpio.
        abort_if($slack === null || $slack === '', 404);

        return app('distributor')->enterprises()->where('enterprises.slack', $slack)->firstOrFail();
    }

    /** Inscripción cuyo usuario pertenece a una empresa del distribuidor, o 404. */
    private function managedInscription(?string $slack): Inscription
    {
        // El slack llega de un POST sin validar: sin este guard, pasar
        // null a un parámetro `string` lanzaba TypeError y devolvía un
        // 500 en vez de un 404 limpio.
        abort_if($slack === null || $slack === '', 404);

        $enterpriseIds = app('distributor')->enterprises()->pluck('enterprises.id')->all();

        return Inscription::where('slack', $slack)
            ->whereExists(fn ($q) => $q->selectRaw('1')->from('enterprise_user')
                ->whereColumn('enterprise_user.user_id', 'inscriptions.user_id')
                ->whereIn('enterprise_user.enterprise_id', $enterpriseIds))
            ->firstOrFail();
    }

    public function index(Request $request, $slack)
    {

        $searchKey = $request->search;
        $enterprise = $this->managedEnterprise($slack);

        $courses = $enterprise->courses()->descending();

        if ($searchKey != null) {
            $courses = $courses->where('title', 'like', '%'.$searchKey.'%');
        }

        $courses = $courses->paginate(paginationNumber());

        return view('distributors.views.enterprises.courses.index')->with([
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
        $enterprise = $this->managedEnterprise($enterprise);
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

        return view('distributors.views.enterprises.courses.view')->with([
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

        $inscription = $this->managedInscription($slack);
        $progress = $inscription->progress;
        $user = $inscription->user;
        $course = $inscription->course;
        $class = $course->lessons;

        return view('distributors.views.enterprises.courses.progress')->with([
            'user' => $user,
            'course' => $course,
            'progress' => $progress,
            'class' => $class,
            'inscription' => $inscription,
        ]);

    }

    public function reasign($enterprise, $course)
    {

        $enterprise = $this->managedEnterprise($enterprise);
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

        return view('distributors.views.enterprises.courses.reasign')->with([
            'courses' => $courses,
            'users' => $users,
            'enterprise' => $enterprise,
            'course' => $course,
        ]);

    }

    public function includes(Request $request, InscriptionService $inscriptions)
    {

        // Ownership: empresa del distribuidor + curso asignado a esa empresa.
        $enterprise = $this->managedEnterprise($request->enterprise);
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

    public function generate(Request $request)
    {
        // Ownership: la empresa debe pertenecer al distribuidor autenticado
        // (evita descargar el reporte de progreso de una empresa ajena).
        $enterprise = app('distributor')->enterprises()->where('enterprises.id', $request->enterprise)->firstOrFail();
        $course = $request->course;
        $modalitie = $request->modalitie;

        return Excel::download(new CoursesExport($course, $enterprise->id, $modalitie), 'REPORTE CURSO '.date('Y-m-d').'.xlsx');
    }

    public function destroy($enterprise, $course)
    {

        $enterprise = $this->managedEnterprise($enterprise);
        $course = Course::slack($course);

        $inscription = EnterpriseCourse::validate($enterprise->id, $course->id);

        if ($inscription) {
            $inscription->delete();
        }

        // Bug: redirigía a manager.enterprises.courses (dominio Managers) en
        // vez de la ruta equivalente de este portal -- un distribuidor sin
        // rol manager caía en el middleware IsManager y terminaba en /validation.
        return redirect()->route('distributor.enterprises.courses', $enterprise->slack);
    }

    public function bulkAction(BulkActionEnterpriseCourseRequest $request, $slack): JsonResponse
    {
        // Ownership: la empresa debe pertenecer al distribuidor (evita IDOR por slack).
        $enterprise = $this->managedEnterprise($slack);

        $count = EnterpriseCourse::where('enterprise_id', $enterprise->id)
            ->whereIn('course_id', $request->ids)
            ->count();

        EnterpriseCourse::where('enterprise_id', $enterprise->id)
            ->whereIn('course_id', $request->ids)
            ->delete();

        return response()->json(['success' => true, 'message' => $count.' curso(s) quitados de la empresa.']);
    }

    public function details($slack)
    {

        $inscription = $this->managedInscription($slack);
        $progress = $inscription->progress;
        $user = $inscription->user;
        $course = $inscription->course;
        $class = $course->lessons;

        return view('distributors.views.enterprises.courses.details')->with([
            'user' => $user,
            'course' => $course,
            'class' => $class,
            'inscription' => $inscription,
            'progress' => $progress,
        ]);

    }

    public function assign($slack)
    {

        $distributor = app('distributor');
        $courses = $distributor->courses;

        $enterprise = $this->managedEnterprise($slack);
        $course = $enterprise->courses;

        $courses = $courses->pluck('title', 'id');

        return view('distributors.views.enterprises.courses.assign')->with([
            'enterprise' => $enterprise,
            'courses' => $courses,
            'course' => $course,
        ]);

    }

    public function update(Request $request)
    {
        // Ownership: solo empresas del distribuidor (evita IDOR por slack).
        $enterprise = $this->managedEnterprise($request->slack);

        $currentCourses = $enterprise->courses->pluck('id')->toArray();

        $requestedCourses = $request->courses ? explode(',', $request->courses) : [];

        // Bypass de facturación: sin este filtro, un distribuidor podía
        // asignar a su empresa CUALQUIER curso del catálogo global (no solo
        // los de su propio catálogo contratado en distributor_courses), y
        // ese curso quedaba disponible para matricular gratis vía
        // includes()/InscriptionService::enrollSimple() sin pasar nunca por
        // DistributorCourse::tariff() ni generar una Order con importe > 0.
        $distributorCourseIds = app('distributor')->courses()->pluck('courses.id')->map(fn ($id) => (string) $id)->all();
        $newCourses = array_values(array_intersect($requestedCourses, $distributorCourseIds));

        if (! empty($newCourses)) {

            $toDetach = array_diff($currentCourses, $newCourses);

            // sync() en una sola operación atómica dentro de una transacción:
            // el detach()+attach() en loop suelto podía dejar a la empresa
            // con un subconjunto de cursos desincronizado (ni lo pedido ni lo
            // que tenía antes) si un attach() a mitad de camino fallaba --
            // mismo patrón ya corregido en BundlesController::update(), con
            // el agravante aquí de que ya hay lógica de facturación encima
            // (filtro $distributorCourseIds).
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
