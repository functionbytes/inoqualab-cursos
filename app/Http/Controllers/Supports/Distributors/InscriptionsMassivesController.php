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

class InscriptionsMassivesController extends Controller
{
    public function index($slack)
    {

        $distributor = Distributor::slack($slack);
        $enterprises = $distributor->enterprises;
        $enterprises->prepend('', '');
        $enterprises = $enterprises->pluck('title', 'slack');

        return view('supports.views.distributors.inscriptions.massives.index')->with([
            'enterprises' => $enterprises,
            'distributor' => $distributor,
        ]);

    }

    public function enroll(Request $request)
    {

        $enterprise = Enterprise::id($request->enterprise);
        $course = Course::id($request->course);
        $user = User::id($request->user);
        $distributor = Distributor::id($request->distributor);

        // Ownership: la empresa debe pertenecer al distribuidor y el usuario a la empresa.
        abort_unless($distributor->enterprises()->where('enterprises.id', $enterprise->id)->exists(), 404, 'La empresa no pertenece al distribuidor.');
        abort_unless($enterprise->users()->where('users.id', $user->id)->exists(), 404, 'El usuario no pertenece a la empresa.');

        $tariff = DistributorCourse::tariff($course->id, $distributor->id);
        abort_if($tariff === null, 422, 'El curso no tiene una tarifa asignada para este distribuidor.');
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
        $distributor = Distributor::slack($request->distributor);

        // Ownership: la empresa debe pertenecer al distribuidor.
        abort_unless($distributor->enterprises()->where('enterprises.id', $enterprise->id)->exists(), 404, 'La empresa no pertenece al distribuidor.');

        $courses = array_filter(array_map('trim', explode(',', $request->courses)));
        $users = array_reverse(array_filter(array_map('trim', explode(',', $request->users))));

        $staff = Auth::user();
        $responses = [];
        $errors = [];

        foreach ($users as $userId) {

            // User::identification() (y Course::id() más abajo) abortan con 404
            // si no hay match -- eso hacía que el "! $user instanceof User" de
            // abajo fuera código muerto inalcanzable: una identificación con
            // typo abortaba el request COMPLETO con un 404 crudo, dejando las
            // matrículas/órdenes YA creadas por iteraciones previas (cada una
            // con su propia DB::transaction()) y el resto del lote sin
            // procesar, en vez de sumarse a $errors[] como el resto de fallos
            // que esta misma función ya reporta con gracia.
            $user = User::where('identification', $userId)->first();

            // Ownership: se omiten (sin abortar el lote) los usuarios ajenos a la empresa.
            if (! $user instanceof User || ! $enterprise->users()->where('users.id', $user->id)->exists()) {
                continue;
            }

            foreach ($courses as $courseId) {
                $course = Course::where('id', $courseId)->first();

                if (! $course) {
                    $errors[] = [
                        'enterprise_enroll' => $enterprise->id,
                        'course_enroll' => $courseId,
                        'customer_enroll' => $user->id,
                        'customer_identification' => $user->identification,
                        'message' => 'No existe ningún curso con ese id.',
                    ];

                    continue;
                }

                $existingInscription = Inscription::existingInscription($user->id, $course->id)->first();

                if ($existingInscription) {
                    $errors[] = [
                        'enterprise_enroll' => $enterprise->id,
                        'course_enroll' => $course->id,
                        'customer_name' => $user->firstname.' '.$user->lastname,
                        'customer_enroll' => $user->id,
                        'customer_identification' => $user->identification,
                        'course' => $course->title,
                        'enroll_start' => $existingInscription->enroll_start,
                        'enroll_expire' => $existingInscription->enroll_expire,
                        'message' => 'El usuario ya tiene una orden registrada en el periodo especificado.',
                    ];

                    continue;
                }

                $tariff = DistributorCourse::tariff($course->id, $distributor->id);

                if ($tariff === null) {
                    $errors[] = [
                        'enterprise_enroll' => $enterprise->id,
                        'course_enroll' => $course->id,
                        'customer_enroll' => $user->id,
                        'customer_identification' => $user->identification,
                        'course' => $course->title,
                        'message' => 'El curso no tiene una tarifa asignada para este distribuidor.',
                    ];

                    continue;
                }

                $condition = OrderCondition::slug('payment');
                $type = OrderType::slug('services');
                $method = OrderMethod::slug('credit');

                $order = new Order;
                $order->slack = $this->generate_slack('orders');
                $order->number = $this->generate_number('orders');
                $order->reference = 'FAC'.$order->number;
                $order->user_id = $user->id;
                $order->type_id = $type->id;
                $order->method_id = $method->id;
                $order->condition_id = $condition->id;
                $order->transaction = null;
                $order->payment_at = now()->setTimezone('America/Bogota');
                $order->total_discount_amount = 0;
                $order->total_after_discount = $tariff;
                $order->total_before_discount = $tariff;
                $order->total_tax_amount = 0;
                $order->total_order_amount = $tariff;

                $orderItem = new OrderItem;
                $orderItem->slack = $this->generate_slack('order_items');
                $orderItem->item_id = $course->id;
                $orderItem->item_type = Course::class;
                $orderItem->quantity = 1;
                $orderItem->amount = $tariff;

                $inscription = new Inscription;
                $inscription->slack = $this->generate_slack('inscriptions');
                $inscription->user_id = $user->id;
                $inscription->course_id = $course->id;
                $inscription->percent = 0;
                $inscription->enroll_start = now()->setTimezone('America/Bogota');
                $inscription->enroll_expire = now()->setTimezone('America/Bogota')->addMonths(3);
                $inscription->enroll_culminated = null;
                $inscription->culminated = 0;

                $reportItem = new OrderActivity;
                $reportItem->slack = $this->generate_slack('orders_activity');
                $reportItem->distributor_id = $distributor->id;
                $reportItem->course_id = $course->id;
                $reportItem->enterprise_id = $enterprise->id;
                $reportItem->item_type = Distributor::class;
                $reportItem->id_type = $distributor->id;
                $reportItem->staff_id = $staff->id;
                $reportItem->relation_id = 0;
                $reportItem->invoiced = 0;
                $reportItem->invoiced_at = null;

                $inscription = DB::transaction(function () use ($order, $orderItem, $inscription, $reportItem) {
                    $order->save();
                    $orderItem->order_id = $order->id;
                    $orderItem->save();
                    $inscription->order_id = $order->id;
                    $inscription->save();
                    $reportItem->order_id = $order->id;
                    $reportItem->save();

                    return $inscription;
                });

                InscriptionCreated::dispatch($inscription);

                $responses[] = [
                    'user' => $user->identification,
                    'course' => $course->title,
                    'message' => 'Inscripción realizada correctamente.',
                ];
            }
        }

        return response()->json([
            'success' => empty($errors),
            'responses' => $responses,
            'errors' => $errors,
        ]);
    }

    public static function getCourses(Request $request)
    {

        if ($request->enterprise != null) {
            $courses = Enterprise::slack($request->enterprise)->courses()->limit(500)->get();
            $formatted_courses = [];
            $formatted_courses[] = ['id' => '', 'text' => ''];
            foreach ($courses as $course) {
                $formatted_courses[] = ['id' => $course->id, 'text' => strtoupper($course->title)];
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
            $enterprise = Enterprise::slack($request->enterprise); // Asegúrate de obtener la empresa.

            if ($enterprise) {

                $users = $enterprise->users()->orderBy('created_at', 'desc')->limit(500)->get();

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
