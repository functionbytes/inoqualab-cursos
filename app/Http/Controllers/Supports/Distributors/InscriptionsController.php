<?php

namespace App\Http\Controllers\Supports\Distributors;

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

        $distributor = Distributor::slack($slack);
        abort_unless($distributor instanceof Distributor, 404);
        $enterprises = $distributor->enterprises;
        $enterprises->prepend('', '');
        $enterprises = $enterprises->pluck('title', 'slack');

        return view('supports.views.distributors.inscriptions.inscriptions.index')->with([
            'enterprises' => $enterprises,
            'distributor' => $distributor,
        ]);

    }

    public function enroll(Request $request)
    {

        $enterprise = Enterprise::id($request->enterprise);
        abort_unless($enterprise instanceof Enterprise, 404);
        $course = Course::id($request->course);
        abort_unless($course instanceof Course, 404);
        $user = User::id($request->user);
        abort_unless($user instanceof User, 404);
        $distributor = Distributor::id($request->distributor);
        abort_unless($distributor instanceof Distributor, 404);

        // Ownership: la empresa debe pertenecer al distribuidor y el usuario a la
        // empresa (mismo patrón que los flujos self-service de Distributors). Sin
        // esto, soporte podía matricular a cualquier usuario en cualquier empresa.
        abort_unless($distributor->enterprises()->where('enterprises.id', $enterprise->id)->exists(), 404, 'La empresa no pertenece al distribuidor.');
        abort_unless($enterprise->users()->where('users.id', $user->id)->exists(), 404, 'El usuario no pertenece a la empresa.');

        $tariff = DistributorCourse::tariff($course->id, $distributor->id);
        abort_if($tariff === null, 422, 'El curso no tiene una tarifa asignada para este distribuidor.');
        $condition = OrderCondition::slug('payment');
        abort_unless($condition instanceof OrderCondition, 404);
        $type = OrderType::slug('services');
        abort_unless($type instanceof OrderType, 404);
        $method = OrderMethod::slug('credit');
        abort_unless($method instanceof OrderMethod, 404);
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

        [$inscription, $isNew] = DB::transaction(function () use ($order, $orderitem, $reportitem, $user, $course) {
            $now = Carbon::now()->setTimezone('America/Bogota');
            $order->save();
            $orderitem->order_id = $order->id;
            $orderitem->save();
            // Reutiliza la inscripción existente (extiende vigencia + renueva
            // certificado) o crea una nueva. Evita inscripciones duplicadas.
            [$inscription, $isNew] = $this->renewOrCreateInscription($user->id, $course->id, $order->id, $now);
            $reportitem->order_id = $order->id;
            $reportitem->save();

            return [$inscription, $isNew];
        });

        // El correo de bienvenida solo en creación nueva, no en renovación.
        if ($isNew) {
            InscriptionCreated::dispatch($inscription);
        }

        return response()->json([
            'success' => true,
            'message' => 'Se ha reinscrito correctamente ',
        ]);

    }

    public function store(Request $request)
    {

        $enterprise = Enterprise::slack($request->enterprise);
        abort_unless($enterprise instanceof Enterprise, 404);
        $distributor = Distributor::slack($request->distributor);
        abort_unless($distributor instanceof Distributor, 404);
        $course = Course::id($request->course);
        abort_unless($course instanceof Course, 404);

        $user = User::identification($request->user);
        abort_unless($user instanceof User, 404);

        // Ownership: la empresa debe pertenecer al distribuidor y el usuario a la empresa.
        abort_unless($distributor->enterprises()->where('enterprises.id', $enterprise->id)->exists(), 404, 'La empresa no pertenece al distribuidor.');
        abort_unless($enterprise->users()->where('users.id', $user->id)->exists(), 404, 'El usuario no pertenece a la empresa.');

        $existingInscription = Inscription::existingInscription($user->id, $course->id)->first();

        if ($existingInscription) {

            $response = [
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
            ];

            return response()->json($response);

        }

        $tariff = DistributorCourse::tariff($course->id, $distributor->id);
        abort_if($tariff === null, 422, 'El curso no tiene una tarifa asignada para este distribuidor.');
        $condition = OrderCondition::slug('payment');
        abort_unless($condition instanceof OrderCondition, 404);
        $type = OrderType::slug('services');
        abort_unless($type instanceof OrderType, 404);
        $method = OrderMethod::slug('credit');
        abort_unless($method instanceof OrderMethod, 404);
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

        [$inscription, $isNew] = DB::transaction(function () use ($order, $orderitem, $reportitem, $user, $course) {
            $now = Carbon::now()->setTimezone('America/Bogota');
            $order->save();
            $orderitem->order_id = $order->id;
            $orderitem->save();
            // Reutiliza la inscripción existente (extiende vigencia + renueva
            // certificado) o crea una nueva. Evita inscripciones duplicadas.
            [$inscription, $isNew] = $this->renewOrCreateInscription($user->id, $course->id, $order->id, $now);
            $reportitem->order_id = $order->id;
            $reportitem->save();

            return [$inscription, $isNew];
        });

        // El correo de bienvenida solo en creación nueva, no en renovación.
        if ($isNew) {
            InscriptionCreated::dispatch($inscription);
        }

        return response()->json([
            'success' => true,
            'message' => 'Se ha inscrito correctamente ',
        ]);

    }

    public static function getCourses(Request $request)
    {

        if ($request->enterprise != null) {
            $enterprise = Enterprise::slack($request->enterprise);
            $formatted_courses = [];
            $formatted_courses[] = ['id' => '', 'text' => ''];
            if ($enterprise instanceof Enterprise) {
                foreach ($enterprise->courses as $course) {
                    $formatted_courses[] = ['id' => $course->id, 'text' => strtoupper($course->title)];
                }
            }
        } else {
            $formatted_courses = [];
        }

        return \Response::json($formatted_courses);
    }

    public static function getUsers(Request $request)
    {
        $formatted_users = [['id' => '', 'text' => '']];

        if ($request->enterprise != null) {
            $enterprise = Enterprise::slack($request->enterprise);

            if ($enterprise instanceof Enterprise) {
                $users = $enterprise->users()->orderBy('created_at', 'desc')->get();

                foreach ($users as $user) {
                    if ($user->identification != null) {
                        $formatted_users[] = ['id' => $user->identification, 'text' => $user->identification];
                    }
                }
            }
        }

        return \Response::json($formatted_users);
    }
}
