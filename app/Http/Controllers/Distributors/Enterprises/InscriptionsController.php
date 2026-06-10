<?php

namespace App\Http\Controllers\Distributors\Enterprises;

use App\Events\Inscriptions\InscriptionCreated;
use App\Http\Controllers\Controller;
use App\Models\Course\Course;
use App\Models\Distributor\Distributor;
use App\Models\Distributor\DistributorCourse;
use App\Models\Enterprise\Enterprise;
use App\Models\Inscription;
use App\Models\Order\Order;
use App\Models\Order\OrderActivity;
use App\Models\Order\OrderCondition;
use App\Models\Order\OrderItem;
use App\Models\Order\OrderMethod;
use App\Models\Order\OrderType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InscriptionsController extends Controller
{
    public function index($slack)
    {

        $enterprise = Enterprise::slack($slack);
        $users = $enterprise->users()->available()->get();
        $courses = $enterprise->courses()->available()->get();
        $users = $users->pluck('identification', 'identification');
        $courses = $courses->pluck('title', 'id');

        count($users) > 0 ? $users->prepend('', '') : $users;
        count($courses) > 0 ? $courses->prepend('', '') : $courses;

        return view('distributors.views.enterprises.enterprises.inscription')->with([
            'enterprise' => $enterprise,
            'users' => $users,
            'courses' => $courses,
        ]);

    }

    public function enroll(Request $request)
    {

        $enterprise = Enterprise::id($request->enterprise);
        $course = Course::id($request->course);
        $user = User::id($request->user);
        $distributor = app('distributor');

        $tariff = DistributorCourse::tariff($course->id, $distributor->id);
        $condition = OrderCondition::slug('payment');
        $type = OrderType::slug('services');
        $method = OrderMethod::slug('credit');
        $staff = Auth::user();

        $order = new Order;
        $order->slack = $this->generate_slack('orders');
        $order->number = $this->generate_number('orders');
        $order->reference = 'FAC'.$order->number;
        $order->user_id = $user->id;
        $order->type_id = $type->id;
        $order->method_id = $method->id;
        $order->condition_id = $condition->id;
        $order->transaction = null;
        $order->payment_at = Carbon::now()->setTimezone('America/Bogota');
        $order->total_discount_amount = 0;
        $order->total_after_discount = $tariff;
        $order->total_before_discount = $tariff;
        $order->total_tax_amount = 0;
        $order->total_order_amount = $tariff;
        $order->created_at = Carbon::now()->setTimezone('America/Bogota');
        $order->updated_at = Carbon::now()->setTimezone('America/Bogota');

        $orderitem = new OrderItem;
        $orderitem->slack = $this->generate_slack('order_items');
        $orderitem->item_id = $course->id;
        $orderitem->item_type = Course::class;
        $orderitem->quantity = 1;
        $orderitem->amount = $tariff;
        $orderitem->created_at = Carbon::now()->setTimezone('America/Bogota');
        $orderitem->updated_at = Carbon::now()->setTimezone('America/Bogota');

        $inscription = new Inscription;
        $inscription->slack = $this->generate_slack('inscriptions');
        $inscription->user_id = $user->id;
        $inscription->course_id = $course->id;
        $inscription->percent = 0;
        $inscription->enroll_start = Carbon::now()->setTimezone('America/Bogota');
        $inscription->enroll_expire = Carbon::now()->setTimezone('America/Bogota')->addMonths(3);
        $inscription->enroll_culminated = null;
        $inscription->culminated = 0;
        $inscription->created_at = Carbon::now()->setTimezone('America/Bogota');
        $inscription->updated_at = Carbon::now()->setTimezone('America/Bogota');

        $reportitem = new OrderActivity;
        $reportitem->slack = $this->generate_slack('orders_activity');
        $reportitem->distributor_id = $distributor->id;
        $reportitem->course_id = $course->id;
        $reportitem->enterprise_id = $enterprise->id;
        $reportitem->item_type = Distributor::class;
        $reportitem->id_type = $distributor->id;
        $reportitem->staff_id = $staff->id;
        $reportitem->relation_id = 0;
        $reportitem->invoiced = 0;
        $reportitem->invoiced_at = null;
        $reportitem->created_at = Carbon::now()->setTimezone('America/Bogota');
        $reportitem->updated_at = Carbon::now()->setTimezone('America/Bogota');

        $inscription = DB::transaction(function () use ($order, $orderitem, $inscription, $reportitem) {
            $order->save();
            $orderitem->order_id = $order->id;
            $orderitem->save();
            $inscription->order_id = $order->id;
            $inscription->save();
            $reportitem->order_id = $order->id;
            $reportitem->save();

            return $inscription;
        });

        InscriptionCreated::dispatch($inscription);

        return response()->json([
            'success' => true,
            'message' => 'Se ha reinscrito correctamente ',
        ]);

    }

    public function store(Request $request)
    {

        $enterprise = Enterprise::slack($request->enterprise);
        $course = Course::id($request->course);
        $distributor = app('distributor');

        $user = User::identification($request->user);

        $existingInscription = Inscription::existingInscription($user->id, $course->id)->first();

        if ($existingInscription) {

            return response()->json([
                'data' => [
                    'enterprise_enroll' => $enterprise->id,
                    'course_enroll' => $course->id,
                    'customer_enroll' => $user->id,
                    'identification' => $user->identification,
                    'course' => $course->title,
                    'enroll_start' => $existingInscription->enroll_start,
                    'enroll_expire' => $existingInscription->enroll_expire,
                ],
                'success' => false,
                'message' => 'El usuario ya tiene una orden registrada en el periodo especificado.',
            ]);

        }

        $tariff = DistributorCourse::tariff($course->id, $distributor->id);
        $condition = OrderCondition::slug('payment');
        $type = OrderType::slug('services');
        $method = OrderMethod::slug('credit');
        $staff = Auth::user();

        $order = new Order;
        $order->slack = $this->generate_slack('orders');
        $order->number = $this->generate_number('orders');
        $order->reference = 'FAC'.$order->number;
        $order->user_id = $user->id;
        $order->type_id = $type->id;
        $order->method_id = $method->id;
        $order->condition_id = $condition->id;
        $order->transaction = null;
        $order->payment_at = Carbon::now()->setTimezone('America/Bogota');
        $order->total_discount_amount = 0;
        $order->total_after_discount = $tariff;
        $order->total_before_discount = $tariff;
        $order->total_tax_amount = 0;
        $order->total_order_amount = $tariff;
        $order->created_at = Carbon::now()->setTimezone('America/Bogota');
        $order->updated_at = Carbon::now()->setTimezone('America/Bogota');

        $orderitem = new OrderItem;
        $orderitem->slack = $this->generate_slack('order_items');
        $orderitem->item_id = $course->id;
        $orderitem->item_type = Course::class;
        $orderitem->quantity = 1;
        $orderitem->amount = $tariff;
        $orderitem->created_at = Carbon::now()->setTimezone('America/Bogota');
        $orderitem->updated_at = Carbon::now()->setTimezone('America/Bogota');

        $inscription = new Inscription;
        $inscription->slack = $this->generate_slack('inscriptions');
        $inscription->user_id = $user->id;
        $inscription->course_id = $course->id;
        $inscription->percent = 0;
        $inscription->enroll_start = Carbon::now()->setTimezone('America/Bogota');
        $inscription->enroll_expire = Carbon::now()->setTimezone('America/Bogota')->addMonths(3);
        $inscription->enroll_culminated = null;
        $inscription->culminated = 0;
        $inscription->created_at = Carbon::now()->setTimezone('America/Bogota');
        $inscription->updated_at = Carbon::now()->setTimezone('America/Bogota');

        $reportitem = new OrderActivity;
        $reportitem->slack = $this->generate_slack('orders_activity');
        $reportitem->distributor_id = $distributor->id;
        $reportitem->course_id = $course->id;
        $reportitem->enterprise_id = $enterprise->id;
        $reportitem->item_type = Distributor::class;
        $reportitem->id_type = $distributor->id;
        $reportitem->staff_id = $staff->id;
        $reportitem->relation_id = 0;
        $reportitem->invoiced = 0;
        $reportitem->invoiced_at = null;
        $reportitem->created_at = Carbon::now()->setTimezone('America/Bogota');
        $reportitem->updated_at = Carbon::now()->setTimezone('America/Bogota');

        $inscription = DB::transaction(function () use ($order, $orderitem, $inscription, $reportitem) {
            $order->save();
            $orderitem->order_id = $order->id;
            $orderitem->save();
            $inscription->order_id = $order->id;
            $inscription->save();
            $reportitem->order_id = $order->id;
            $reportitem->save();

            return $inscription;
        });

        InscriptionCreated::dispatch($inscription);

        return response()->json([
            'success' => true,
            'message' => 'Se ha inscrito correctamente ',
        ]);

    }

    public static function getCourses(Request $request)
    {

        if ($request->enterprise != null) {
            $courses = Enterprise::slack($request->enterprise)->courses;
            $formatted_courses = [];
            $formatted_courses[] = ['id' => '', 'text' => ''];
            foreach ($courses as $course) {
                $formatted_courses[] = ['id' => $course->id, 'text' => $course->title];
            }
        } else {
            $formatted_courses = [];
        }

        return \Response::json($formatted_courses);
    }

    public static function getUsers(Request $request)
    {

        if ($request->enterprise != null) {
            $users = Enterprise::slack($request->enterprise)->users;

            $formatted_users = [];
            $formatted_users[] = ['id' => '', 'text' => ''];
            foreach ($users as $user) {
                if ($user->identification != null) {
                    $formatted_users[] = ['id' => $user->identification, 'text' => $user->identification];
                }
            }
        } else {
            $formatted_users = [];
        }

        return \Response::json($formatted_users);
    }
}
