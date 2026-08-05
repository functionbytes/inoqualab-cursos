<?php

namespace App\Http\Controllers\Managers\Enterprises;

use App\Exports\Managers\CoursesExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Enterprises\ImportCoursesRequest;
use App\Imports\Managers\CoursesImport;
use App\Models\Course\Course;
use App\Models\Enterprise\Enterprise;
use App\Models\Enterprise\EnterpriseCourse;
use App\Models\Exam\ExamAnswer;
use App\Models\Inscription;
use App\Models\Order\Order;
use App\Models\Quiz\QuizAnswer;
use App\Models\User;
use App\Services\InscriptionService;
use App\Structure\Courses;
use App\Structure\Users;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class CourseController extends Controller
{
    public function __construct(
        private readonly InscriptionService $inscriptionService
    ) {}

    public function index(Request $request, $slack)
    {
        abort_unless(auth()->user()->can('enterprises.view'), 403);

        $searchKey = $request->search;
        $enterprise = Enterprise::slack($slack);

        $courses = $enterprise->courses();

        if ($searchKey != null) {
            $courses = $courses->where('title', 'like', '%'.$searchKey.'%');
        }

        $courses = $courses->paginate(paginationNumber());

        return view('managers.views.enterprises.courses.index')->with([
            'enterprise' => $enterprise,
            'courses' => $courses,
            'searchKey' => $searchKey,
        ]);

    }

    public function create($slack)
    {
        abort_unless(auth()->user()->can('enterprises.create'), 403);

        $enterprise = Enterprise::slack($slack);

        $courses = $enterprise->courses;
        $alls = Course::select('id', 'title', 'slug')->orderBy('title')->get();

        $validatealls = collect();
        $validatecourses = collect();

        foreach ($courses as $item) {

            $slack = $item->slack;
            $id = $item->id;
            $title = $item->title;
            $slug = $item->slug;

            $add = CourseController::createCourses($slack, $id, $title, $slug);
            $validatecourses->prepend($add);
        }

        foreach ($alls as $item) {

            $slack = $item->slack;
            $id = $item->id;
            $title = $item->title;
            $slug = $item->slug;

            $adds = CourseController::createCourses($slack, $id, $title, $slug);
            $validatealls->prepend($adds);
        }

        $validatesall = $validatealls->pluck('title', 'id');
        $allcourses = $validatecourses->pluck('title', 'id');

        $listcourses = $validatesall->diffKeys($allcourses);

        return view('managers.views.enterprises.courses.create')->with([
            'enterprise' => $enterprise,
            'courses' => $listcourses,
        ]);

    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('enterprises.create'), 403);
        $enterprise = Enterprise::slack($request->enterprise);
        $course = Course::id($request->course);

        $inscription = new EnterpriseCourse;
        $inscription->course_id = $course->id;
        $inscription->enterprise_id = $enterprise->id;
        $inscription->created_at = Carbon::now()->setTimezone('America/Bogota');
        $inscription->updated_at = Carbon::now()->setTimezone('America/Bogota');
        $inscription->save();

        return redirect()->route('manager.enterprises.courses', $enterprise->slack);

    }

    public function user(Request $request, $slack)
    {
        abort_unless(auth()->user()->can('enterprises.view'), 403);

        $searchKey = $request->search;

        $user = User::with('orders.inscriptions')->slack($slack);
        $allInscriptions = $user->orders->flatMap->inscriptions;

        return view('managers.views.enterprises.courses.courses')->with([
            'orders' => $allInscriptions,
        ]);

    }

    public function view(Request $request, $enterprise, $course)
    {
        abort_unless(auth()->user()->can('enterprises.view'), 403);

        $searchKey = $request->search;
        $culminate = $request->culminate;
        $enterprise = Enterprise::slack($enterprise);
        $course = Course::slack($course);

        $users = User::query()
            ->join('enterprise_user', fn ($j) => $j->on('users.id', '=', 'enterprise_user.user_id'))
            ->where('enterprise_user.enterprise_id', $enterprise->id)
            ->join('inscriptions', fn ($j) => $j->on('users.id', '=', 'inscriptions.user_id'))
            ->join('orders', fn ($j) => $j->on('orders.id', '=', 'inscriptions.order_id'))
            ->where('inscriptions.course_id', $course->id)
            ->select(
                'users.slack',
                'users.firstname',
                'users.lastname',
                'users.available',
                'users.identification',
                'inscriptions.id',
                'orders.slack as order_slack',
                // CertificatesController::user() y CourseController::progress()
                // esperan el slack de la INSCRIPCIÓN, no del usuario; sin esto
                // los links "Certificado"/"Reporte" de la vista pasaban
                // $user->slack y siempre daban 404 o 500.
                'inscriptions.slack as inscription_slack',
                'inscriptions.enroll_start',
                'inscriptions.enroll_expire',
                'inscriptions.enroll_culminated',
                'inscriptions.percent',
                'inscriptions.order_id',
                'inscriptions.updated_at',
                'inscriptions.culminated',
                'inscriptions.created_at'
            )->orderBy('inscriptions.enroll_culminated', 'desc');

        if ($searchKey) {
            $users = $users->where(function ($q) use ($searchKey) {
                $q->where('users.firstname', 'like', '%'.$searchKey.'%')
                    ->orWhere('users.lastname', 'like', '%'.$searchKey.'%')
                    ->orWhere('users.email', $searchKey)
                    ->orWhere('users.identification', $searchKey);
            });
        }

        if ($culminate != null) {
            $users = $users->where('inscriptions.culminated', $culminate);
        }

        $users = $users->paginate(paginationNumber());

        return view('managers.views.enterprises.courses.view')->with([
            'course' => $course,
            'culminate' => $culminate,
            'users' => $users,
            'count' => $users,
            'enterprise' => $enterprise,
            'searchKey' => $searchKey,
        ]);

    }

    public function progress($slack)
    {
        abort_unless(auth()->user()->can('enterprises.view'), 403);

        // Order no tiene relaciones progress()/course() (checkout multi-item):
        // Order::with(['progress', 'course.lessons']) lanzaba
        // RelationNotFoundException siempre, 500 garantizado. La vista enlaza
        // esta ruta con el slack de la INSCRIPCIÓN (mismo contrato que
        // CertificatesController::user()), no de la orden ni del usuario.
        $inscription = Inscription::slack($slack);
        $progress = $inscription->progress;
        $user = $inscription->user;
        $course = $inscription->course;
        abort_unless($course instanceof Course, 404, 'El curso de esta inscripción ya no existe.');
        $class = $course->lessons;

        return view('managers.views.enterprisesprogress.index')->with([
            'user' => $user,
            'course' => $course,
            'progress' => $progress,
            'class' => $class,
            'inscription' => $inscription,
        ]);

    }

    public function reasign($enterprise, $course)
    {
        abort_unless(auth()->user()->can('enterprises.view'), 403);

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

        return view('managers.views.enterprises.courses.reasign')->with([
            'courses' => $courses,
            'users' => $users,
            'enterprise' => $enterprise,
            'course' => $course,
        ]);

    }

    public function actionReasign(Request $request)
    {
        abort_unless(auth()->user()->can('enterprises.update'), 403);

        $enterprise = Enterprise::id($request->enterprise);
        $old = Course::id($request->old);
        $new = Course::id($request->course);
        $users = $request->users;

        // La asociación con el curso vive en Inscription (orders.course_id no
        // existe), así que la reasignación opera sobre inscripciones, no orders.
        DB::transaction(function () use ($enterprise, $old, $new, $users) {
            $memberIds = $enterprise->users()->pluck('users.id');

            if ($users[0] === '0') {
                $inscriptions = Inscription::where('course_id', $old->id)->whereIn('user_id', $memberIds)->get();
                foreach ($inscriptions as $inscription) {
                    $this->reassignInscription($inscription, $new);
                }

                return;
            }

            foreach ($users as $identifier) {
                $user = User::identification($identifier);

                // Sin esto, se podía reasignar (y borrar respuestas de examen/quiz)
                // de un usuario que no pertenece a $enterprise (IDOR).
                if (! $memberIds->contains($user->id)) {
                    continue;
                }

                $inscriptions = Inscription::where('user_id', $user->id)->where('course_id', $old->id)->get();
                foreach ($inscriptions as $inscription) {
                    $this->reassignInscription($inscription, $new);
                }
            }
        });

        return redirect()->route('manager.enterprises.courses.view', [$enterprise->slack, $old->slack]);
    }

    private function reassignInscription(Inscription $inscription, Course $new): void
    {
        $inscription->course_id = $new->id;
        $inscription->save();

        $certificate = $inscription->certificate;
        if ($certificate !== null) {
            $certificate->course_id = $new->id;
            $certificate->save();
        }

        foreach ($inscription->quizs as $quiz) {
            QuizAnswer::where('quiz_id', $quiz->id)->delete();
            $quiz->delete();
        }

        $exam = $inscription->exam;
        if ($exam !== null) {
            if ($exam->score >= 80) {
                $exam->course_id = $new->id;
                $exam->save();
            } else {
                ExamAnswer::where('exam_id', $exam->id)->delete();
                $exam->delete();
            }
        }
    }

    public static function createCourses($slack, $id, $title, $slug)
    {
        $validate = new Courses;
        $validate->slack = $slack;
        $validate->id = $id;
        $validate->title = $title;
        $validate->slug = $slug;

        return $validate;
    }

    public static function createUsers($slack, $identification, $email)
    {
        $validate = new Users;
        $validate->slack = $slack;
        $validate->identification = $identification;
        $validate->email = $email;

        return $validate;
    }

    public function insert($enterprise, $course)
    {
        abort_unless(auth()->user()->can('enterprises.view'), 403);

        $enterprise = Enterprise::slack($enterprise);
        $course = Course::slack($course);

        $alls = $enterprise->users;
        $users = $alls->pluck('identification', 'identification');

        return view('managers.views.enterprises.courses.insert')->with([
            'users' => $users,
            'enterprise' => $enterprise,
            'course' => $course,
        ]);
    }

    public function postponeCourses($slack)
    {
        abort_unless(auth()->user()->can('enterprises.view'), 403);

        $order = Order::slack($slack);
        $user = $order->user;
        $enterprise = $user->relations;
        // Order no tiene relación course() directa (checkout multi-item vía
        // OrderItem, polimórfico item_type/item_id — OrderItem::course() usa
        // la convención course_id que no existe en la tabla real, siempre
        // null). Se deriva del primer item vía la relación polimórfica real.
        $course = $order->items()->with('itemable')->first()?->itemable;
        // enroll_start/enroll_expire viven en Inscription, no en Order.
        $inscription = Inscription::where('order_id', $order->id)->first();

        return view('managers.views.enterprises.courses.postpone')->with([
            'user' => $user,
            'course' => $course,
            'order' => $order,
            'enterprise' => $enterprise,
            'inscription' => $inscription,
        ]);
    }

    public function postponeUsers($slack)
    {
        abort_unless(auth()->user()->can('enterprises.view'), 403);

        $inscription = Inscription::slack($slack);
        $user = $inscription->user;
        $enterprise = $user->enterprise;
        $course = $inscription->course;

        return view('managers.views.enterprises.courses.action')->with([
            'user' => $user,
            'course' => $course,
            'inscription' => $inscription,
            'enterprise' => $enterprise,
        ]);
    }

    public function actionCourses(Request $request)
    {
        abort_unless(auth()->user()->can('enterprises.update'), 403);

        $date_var = explode(' - ', $request->range);
        abort_unless(count($date_var) === 2, 422, 'Rango de fechas invalido.');

        $inscription = Inscription::slack($request->inscription);
        $inscription->enroll_start = date('Y-m-d', strtotime($date_var[0]));
        $inscription->enroll_expire = date('Y-m-d', strtotime($date_var[1]));
        $inscription->update();

        return response()->json($inscription->slack);
    }

    public function actionUsers(Request $request)
    {
        abort_unless(auth()->user()->can('enterprises.update'), 403);

        $date_var = explode(' - ', $request->range);
        abort_unless(count($date_var) === 2, 422, 'Rango de fechas invalido.');

        // Order no tiene columnas enroll_start/enroll_expire (viven en
        // Inscription); se actualiza la inscripción asociada a esta orden.
        $order = Order::slack($request->order);
        $inscription = Inscription::where('order_id', $order->id)->firstOrFail();
        $inscription->enroll_start = date('Y-m-d', strtotime($date_var[0]));
        $inscription->enroll_expire = date('Y-m-d', strtotime($date_var[1]));
        $inscription->save();

        return response()->json($order->slack);
    }

    public function includes(Request $request): JsonResponse
    {
        abort_unless(auth()->user()->can('enterprises.update'), 403);

        $enterprise = Enterprise::slack($request->enterprise);
        $course = Course::slack($request->course);
        $identifications = explode(',', $request->users);

        $this->inscriptionService->enrollSimpleBulk($identifications, $course, $enterprise);

        return response()->json($enterprise->slack);
    }

    public function report($enterprise, $course)
    {
        abort_unless(auth()->user()->can('enterprises.view'), 403);

        $enterprise = Enterprise::slack($enterprise);
        $course = Course::slack($course);

        $modalities = collect([
            ['id' => '0', 'title' => 'Todos'],
            ['id' => '1', 'title' => 'Culminado'],
            ['id' => '2', 'title' => 'Pendiente'],
        ]);

        $modalities = $modalities->pluck('title', 'id');

        return view('managers.views.enterprises.courses.report')->with([
            'modalities' => $modalities,
            'enterprise' => $enterprise,
            'course' => $course,
        ]);
    }

    public function generate(Request $request)
    {
        abort_unless(auth()->user()->can('enterprises.view'), 403);

        $enterprise = $request->enterprise;
        $course = $request->course;
        $modalitie = $request->modalitie;

        return Excel::download(new CoursesExport($course, $enterprise, $modalitie), 'REPORTE CURSO '.date('Y-m-d').'.xlsx');
    }

    public function import($enterprise, $course)
    {
        abort_unless(auth()->user()->can('enterprises.view'), 403);

        $enterprise = Enterprise::slack($enterprise);
        $course = Course::slack($course);

        return view('managers.views.enterprises.courses.import')->with([
            'enterprise' => $enterprise,
            'course' => $course,
        ]);
    }

    public function importation(ImportCoursesRequest $request)
    {
        abort_unless(auth()->user()->can('enterprises.update'), 403);

        $enterprise = Enterprise::slack($request->validated('enterprise'));
        $course = Course::slack($request->validated('course'));

        try {
            Excel::import(new CoursesImport($enterprise->slack, $course->slack), $request->file('file'));
        } catch (ValidationException $e) {

            $failures = $e->failures();

            return view('managers.views.enterprisesusers.response')->with([
                'error_message' => $e->getMessage(),
                'failures' => $failures,
                'enterprise' => $enterprise,
            ]);
        }

        return redirect()->route('manager.enterprises.courses.view', [$enterprise->slack, $course->slack]);
    }

    public function destroy($enterprise, $course)
    {
        abort_unless(auth()->user()->can('enterprises.delete'), 403);

        $enterprise = Enterprise::slack($enterprise);
        $course = Course::slack($course);

        $inscription = EnterpriseCourse::validate($enterprise->id, $course->id);
        abort_if($inscription === null, 404);
        $inscription->delete();

        return redirect()->route('manager.enterprises.courses', $enterprise->slack);
    }

    public function destroys($slack)
    {
        abort_unless(auth()->user()->can('enterprises.delete'), 403);

        $inscription = Inscription::slack($slack);
        $inscription->delete();

        return back();
    }
}
