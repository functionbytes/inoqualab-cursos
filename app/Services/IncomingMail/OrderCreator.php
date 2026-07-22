<?php

namespace App\Services\IncomingMail;

use App\Events\Inscriptions\InscriptionCreated;
use App\Models\Course\Course;
use App\Models\Distributor\Distributor;
use App\Models\Distributor\DistributorCourse;
use App\Models\Enterprise\Enterprise;
use App\Models\Enterprise\EnterpriseUser;
use App\Models\Inscription;
use App\Models\Mail\IncomingMail;
use App\Models\Order\Order;
use App\Models\Order\OrderActivity;
use App\Models\Order\OrderCondition;
use App\Models\Order\OrderItem;
use App\Models\Order\OrderMethod;
use App\Models\Order\OrderType;
use App\Models\User;
use App\Services\Concerns\GeneratesSlackAndNumber;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OrderCreator
{
    use GeneratesSlackAndNumber;

    /**
     * Create a single Order with multiple OrderItems from a parsed email payload.
     *
     * Returns null if all courses are already enrolled (no new inscriptions).
     *
     * @param  array  $payload  Normalized payload from a MailParserInterface.
     * @param  Course[]  $courses  Already-matched Course models (no nulls).
     */
    public function createFromPayload(
        IncomingMail $mail,
        array $payload,
        Enterprise $enterprise,
        array $courses,
        ?int $staffId = null
    ): ?Order {
        $distributor = $enterprise->distributor;

        if ($distributor === null) {
            throw new \RuntimeException("Empresa sin distribuidor: {$enterprise->title} (id={$enterprise->id})");
        }

        return DB::transaction(function () use ($payload, $enterprise, $distributor, $courses, $staffId) {
            // Resolve user and determine which courses are genuinely new inside
            // the transaction so a failure does not leave an orphan user/order.
            $user = $this->resolveUser($payload, $enterprise);
            $this->ensureEnterpriseUser($user, $enterprise);

            $newCourses = collect($courses)->filter(
                fn ($course) => Inscription::existingInscription($user->id, $course->id)->first() === null
            );

            if ($newCourses->isEmpty()) {
                return null;
            }

            $now = Carbon::now()->setTimezone('America/Bogota');

            $type = OrderType::slug(config('incoming_mail.order_defaults.type_slug'));
            $method = OrderMethod::slug(config('incoming_mail.order_defaults.method_slug'));
            $condition = OrderCondition::slug(config('incoming_mail.order_defaults.condition_slug'));

            $number = $this->generateNumber(Order::class);

            $order = new Order;
            $order->slack = $this->generateSlack(Order::class);
            $order->number = $number;
            $order->reference = 'FAC'.$number;
            $order->user_id = $user->id;
            $order->type_id = $type->id;
            $order->method_id = $method->id;
            $order->condition_id = $condition->id;
            $order->transaction = null;
            $order->payment_at = $now;
            $order->total_discount_amount = 0;
            $order->total_after_discount = 0;
            $order->total_before_discount = 0;
            $order->total_tax_amount = 0;
            $order->total_order_amount = 0;
            $order->created_at = $now;
            $order->updated_at = $now;
            $order->save();

            $total = 0;

            foreach ($newCourses as $course) {
                $tariff = DistributorCourse::tariff($course->id, $distributor->id);

                // Sin tarifa el ítem quedaría en amount null y el total incompleto:
                // se falla la transacción para que el processor marque el correo como
                // no procesable y soporte lo revise, en vez de crear una orden con
                // monto incorrecto en silencio (los enroll manuales también abortan).
                if ($tariff === null) {
                    throw new \RuntimeException("Curso sin tarifa para el distribuidor: {$course->title} (curso id={$course->id}, distribuidor id={$distributor->id})");
                }

                $item = new OrderItem;
                $item->slack = $this->generateSlack(OrderItem::class);
                $item->order_id = $order->id;
                $item->item_type = Course::class;
                $item->item_id = $course->id;
                $item->quantity = 1;
                $item->amount = $tariff;
                $item->created_at = $now;
                $item->updated_at = $now;
                $item->save();

                $inscription = new Inscription;
                $inscription->slack = $this->generateSlack(Inscription::class);
                $inscription->order_id = $order->id;
                $inscription->user_id = $user->id;
                $inscription->course_id = $course->id;
                $inscription->percent = 0;
                $inscription->enroll_start = $now;
                $inscription->enroll_expire = $now->copy()->addMonths(3);
                $inscription->enroll_culminated = null;
                $inscription->culminated = 0;
                $inscription->created_at = $now;
                $inscription->updated_at = $now;
                $inscription->save();

                $activity = new OrderActivity;
                $activity->slack = $this->generateSlack(OrderActivity::class);
                $activity->distributor_id = $distributor->id;
                $activity->order_id = $order->id;
                $activity->course_id = $course->id;
                $activity->enterprise_id = $enterprise->id;
                $activity->item_type = Distributor::class;
                $activity->id_type = $distributor->id;
                $activity->staff_id = $staffId;
                $activity->relation_id = 0;
                $activity->invoiced = 0;
                $activity->invoiced_at = null;
                $activity->created_at = $now;
                $activity->updated_at = $now;
                $activity->save();

                InscriptionCreated::dispatch($inscription);

                $total += $tariff;
            }

            $order->total_after_discount = $total;
            $order->total_before_discount = $total;
            $order->total_order_amount = $total;
            $order->save();

            return $order;
        });
    }

    private function resolveUser(array $payload, Enterprise $enterprise): User
    {
        // where() directo, NO el scope identification(): ese scope hace abort(404)
        // cuando no hay match, lo que impedía crear alumnos nuevos (el caso normal
        // de un correo entrante) — el flujo fallaba antes de llegar al insert.
        $existing = User::where('identification', $payload['document'])->first();

        if ($existing !== null) {
            return $existing;
        }

        $user = new User;
        $user->slack = $this->generateSlack(User::class);
        $user->identification = $payload['document'];
        $user->firstname = $payload['firstname'] ?? $payload['name'] ?? 'Sin nombre';
        $user->lastname = $payload['lastname'] ?? '';
        $user->role = config('incoming_mail.student_role');
        $user->enterprise_id = $enterprise->id;
        $user->available = 1;
        $user->save();

        return $user;
    }

    private function ensureEnterpriseUser(User $user, Enterprise $enterprise): void
    {
        EnterpriseUser::firstOrCreate(
            ['user_id' => $user->id, 'enterprise_id' => $enterprise->id],
            ['available' => 1]
        );
    }
}
