<?php

namespace App\Http\Controllers\Managers\Enterprises;

use App\Exports\Managers\CoursesExport;
use App\Http\Controllers\Controller;
use App\Imports\Managers\CoursesImport;
use App\Models\Course\Course;
use App\Models\Enterprise\Enterprise;
use App\Models\Enterprise\EnterpriseCourse;
use App\Models\ExamAnswer;
use App\Models\Inscription;
use App\Models\Invoice\InvoiceCondition;
use App\Models\Method;
use App\Models\Order\Order;
use App\Models\QuizAnswer;
use App\Models\User;
use App\Structure\Courses;
use App\Structure\Users;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

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

        return view('managers.views.enterprises.courses.index')->with([
            'enterprise' => $enterprise,
            'courses' => $courses,
            'searchKey' => $searchKey,
        ]);

    }

    public function create($slack)
    {
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

        $searchKey = $request->search;

        $user = User::with('orders.inscriptions')->slack($slack);
        $allInscriptions = $user->orders->flatMap->inscriptions;

        return view('managers.views.enterprises.courses.courses')->with([
            'orders' => $allInscriptions,
        ]);

    }

    public function view(Request $request, $enterprise, $course)
    {

        $searchKey = $request->search;
        $culminate = $request->culminate;
        $enterprise = Enterprise::slack($enterprise);
        $course = Course::slack($course);

        $users = DB::table('users')
            ->join('enterprise_user', function ($join) {
                $join->on('users.id', '=', 'enterprise_user.user_id');
            })->where('enterprise_user.enterprise_id', '=', $enterprise->id)
            ->join('inscriptions', function ($join) {
                $join->on('users.id', '=', 'inscriptions.user_id');
            })->join('orders', function ($join) {
                $join->on('orders.id', '=', 'inscriptions.order_id');
            })->where('inscriptions.course_id', '=', $course->id)->select(
                'users.slack',
                'users.firstname',
                'users.lastname',
                'users.available',
                'users.identification',
                'inscriptions.id',
                'orders.slack as slack',
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
            $users = $users->where('users.firstname', 'like', '%'.$searchKey.'%')->orWhere('users.lastname', 'like', '%'.$searchKey.'%')->orWhere('users.email', $searchKey)->orWhere('users.identification', $searchKey);
        }

        if ($culminate != null) {
            $users = $users->where('course_user.culminated', $culminate);
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

        $order = Order::with(['progress', 'user', 'course.lessons'])->slack($slack);
        $progress = $order->progress;
        $user = $order->user;
        $course = $order->course;
        $class = $course->lessons;

        return view('managers.views.enterprisesprogress.index')->with([
            'user' => $user,
            'course' => $course,
            'progress' => $progress,
            'class' => $class,
            'order' => $order,
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

        return view('managers.views.enterprises.courses.reasign')->with([
            'courses' => $courses,
            'users' => $users,
            'enterprise' => $enterprise,
            'course' => $course,
        ]);

    }

    public function actionReasign(Request $request)
    {
        $enterprise = Enterprise::id($request->enterprise);
        $old = Course::id($request->old);
        $new = Course::id($request->course);
        $users = $request->users;

        DB::transaction(function () use ($enterprise, $old, $new, $users) {
            if ($users[0] == 0) {
                $allUsers = EnterpriseCourse::users($enterprise->id, $old->id);
                foreach ($allUsers as $user) {
                    foreach (Order::validates($user->id, $old->id) as $order) {
                        $this->reassignOrder($order, $new);
                    }
                }
            } else {
                foreach ($users as $identifier) {
                    $user = User::identification($identifier);
                    foreach (Order::finds($user->id, $old->id) as $order) {
                        $this->reassignOrder($order, $new);
                    }
                }
            }
        });

        return redirect()->route('manager.enterprises.courses.view', [$enterprise->slack, $old->slack]);
    }

    private function reassignOrder(Order $order, Course $new): void
    {
        $order->course_id = $new->id;
        $order->save();

        $certificate = $order->certificate;
        if ($certificate !== null) {
            $certificate->course_id = $new->id;
            $certificate->save();
        }

        $coursing = $order->coursing;
        if ($coursing !== null) {
            $coursing->course_id = $new->id;
            $coursing->save();
        }

        foreach ($order->quizs as $quiz) {
            QuizAnswer::quiz($quiz->id)->each->delete();
            $quiz->delete();
        }

        foreach ($order->exam as $exam) {
            if ($exam->score >= 80) {
                $exam->course_id = $new->id;
                $exam->save();
            } else {
                ExamAnswer::order($order->id)->each->delete();
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
        $order = Order::slack($slack);
        $user = $order->user;
        $enterprise = $user->relations;
        $course = $order->course;

        return view('managers.views.enterprises.courses.postpone')->with([
            'user' => $user,
            'course' => $course,
            'order' => $order,
            'enterprise' => $enterprise,
        ]);
    }

    public function postponeUsers($slack)
    {
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

        $date_var = explode(' - ', $request->range);
        $inscription = Inscription::slack($request->inscription);
        $inscription->enroll_start = date('Y-m-d', strtotime($date_var[0]));
        $inscription->enroll_expire = date('Y-m-d', strtotime($date_var[1]));
        $inscription->update();

        return response()->json($inscription->slack);
    }

    public function actionUsers(Request $request)
    {
        $date_var = explode(' - ', $request->range);
        $order = Order::slack($request->order);
        $order->enroll_start = date('Y-m-d', strtotime($date_var[0]));
        $order->enroll_expire = date('Y-m-d', strtotime($date_var[1]));
        $order->updated_at = Carbon::now()->setTimezone('America/Bogota');
        $order->save();

        return response()->json($order->slack);
    }

    public function includes(Request $request)
    {
        $enterprise = Enterprise::slack($request->enterprise);
        $course = Course::slack($request->course);
        $condition = InvoiceCondition::slug('pagada');
        $method = Method::slug('acuerdo');

        $users = explode(',', $request->users);

        DB::transaction(function () use ($users, $course, $condition, $method) {
            foreach ($users as $identifier) {
                $validate = User::identification($identifier);

                $order = new Order;
                $order->slack = $this->generate_slack('orders');
                $order->subtotal = 0;
                $order->discount = 0;
                $order->total = 0;
                $order->transaction = null;
                $order->condition_id = $condition->id;
                $order->method_id = $method->id;
                $order->course_id = $course->id;
                $order->user_id = $validate->id;
                $order->enroll_start = Carbon::now()->setTimezone('America/Bogota');
                $order->enroll_expire = Carbon::now()->setTimezone('America/Bogota')->addMonths(3);
                $order->payment_at = Carbon::now()->setTimezone('America/Bogota');
                $order->save();

                $include = new Inscription;
                $include->user_id = $validate->id;
                $include->course_id = $course->id;
                $include->order_id = $order->id;
                $include->culminated = 0;
                $include->culminated_at = null;
                $include->save();
            }
        });

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

        return view('managers.views.enterprises.courses.report')->with([
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

    public function import($enterprise, $course)
    {
        $enterprise = Enterprise::slack($enterprise);
        $course = Course::slack($course);

        return view('managers.views.enterprises.courses.import')->with([
            'enterprise' => $enterprise,
            'course' => $course,
        ]);
    }

    public function importation(Request $request)
    {

        $enterprise = Enterprise::slack($request->enterprise);
        $course = Course::slack($request->course);

        if ($request->file('file')) {

            try {
                Excel::import(new CoursesImport($enterprise->slack, $course->slack), request()->file('file'));
            } catch (ValidationException $e) {

                $failures = $e->failures();

                return view('managers.views.enterprisesusers.response')->with([
                    'error_message' => $e->getMessage(),
                    'failures' => $failures,
                    'enterprise' => $enterprise,
                ]);
            }
        }

        return redirect()->route('manager.enterprises.courses.view', [$enterprise->slack, $course->slack]);
    }

    public function destroy($enterprise, $course)
    {

        $enterprise = Enterprise::slack($enterprise);
        $course = Course::slack($course);

        $inscription = EnterpriseCourse::validate($enterprise->id, $course->id);
        $inscription->delete();

        return redirect()->route('manager.enterprises.courses', $enterprise->slack);
    }

    public function destroys($slack)
    {
        $inscription = Inscription::slack($slack);
        $inscription->delete();

        return back();
    }
}
