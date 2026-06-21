<?php

namespace App\Services;

use App\Events\Inscriptions\InscriptionCreated;
use App\Models\Course\Course;
use App\Models\Distributor\Distributor;
use App\Models\Distributor\DistributorCourse;
use App\Models\Enterprise\Enterprise;
use App\Models\Inscription;
use App\Models\Invoice\InvoiceCondition;
use App\Models\Method;
use App\Models\Order\Order;
use App\Models\Order\OrderActivity;
use App\Models\Order\OrderCondition;
use App\Models\Order\OrderItem;
use App\Models\Order\OrderMethod;
use App\Models\Order\OrderType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InscriptionService
{
    private const TIMEZONE = 'America/Bogota';

    /**
     * Enroll a user into a course via the distributor billing flow.
     * Creates Order + OrderItem + Inscription + OrderActivity and fires InscriptionCreated.
     */
    public function enroll(
        User $user,
        Course $course,
        Enterprise $enterprise,
        Distributor $distributor,
        User $staff
    ): Inscription {
        $tariff = DistributorCourse::tariff($course->id, $distributor->id);
        $condition = OrderCondition::slug('payment');
        $type = OrderType::slug('services');
        $method = OrderMethod::slug('credit');

        $now = Carbon::now()->setTimezone(self::TIMEZONE);

        $order = new Order;
        $order->slack = $this->generateSlack('orders');
        $order->number = $this->generateNumber('orders');
        $order->reference = 'FAC'.$order->number;
        $order->user_id = $user->id;
        $order->type_id = $type->id;
        $order->method_id = $method->id;
        $order->condition_id = $condition->id;
        $order->transaction = null;
        $order->payment_at = $now;
        $order->total_discount_amount = 0;
        $order->total_after_discount = $tariff;
        $order->total_before_discount = $tariff;
        $order->total_tax_amount = 0;
        $order->total_order_amount = $tariff;
        $order->created_at = $now;
        $order->updated_at = $now;

        $orderItem = new OrderItem;
        $orderItem->slack = $this->generateSlack('order_items');
        $orderItem->item_id = $course->id;
        $orderItem->item_type = Course::class;
        $orderItem->quantity = 1;
        $orderItem->amount = $tariff;
        $orderItem->created_at = $now;
        $orderItem->updated_at = $now;

        $inscription = new Inscription;
        $inscription->slack = $this->generateSlack('inscriptions');
        $inscription->user_id = $user->id;
        $inscription->course_id = $course->id;
        $inscription->percent = 0;
        $inscription->enroll_start = $now;
        $inscription->enroll_expire = (clone $now)->addMonths(3);
        $inscription->enroll_culminated = null;
        $inscription->culminated = 0;
        $inscription->created_at = $now;
        $inscription->updated_at = $now;

        $activity = new OrderActivity;
        $activity->slack = $this->generateSlack('orders_activity');
        $activity->distributor_id = $distributor->id;
        $activity->course_id = $course->id;
        $activity->enterprise_id = $enterprise->id;
        $activity->item_type = Distributor::class;
        $activity->id_type = $distributor->id;
        $activity->staff_id = $staff->id;
        $activity->relation_id = 0;
        $activity->invoiced = 0;
        $activity->invoiced_at = null;
        $activity->created_at = $now;
        $activity->updated_at = $now;

        $inscription = DB::transaction(function () use ($order, $orderItem, $inscription, $activity) {
            $order->save();
            $orderItem->order_id = $order->id;
            $orderItem->save();
            $inscription->order_id = $order->id;
            $inscription->save();
            $activity->order_id = $order->id;
            $activity->save();

            return $inscription;
        });

        InscriptionCreated::dispatch($inscription);

        return $inscription;
    }

    /**
     * Enroll a user into a course with duplicate check.
     * Returns null when the user already has an active inscription for the course.
     */
    public function enrollIfNotDuplicate(
        User $user,
        Course $course,
        Enterprise $enterprise,
        Distributor $distributor,
        User $staff
    ): ?Inscription {
        $existing = Inscription::existingInscription($user->id, $course->id)->first();

        if ($existing) {
            return null;
        }

        return $this->enroll($user, $course, $enterprise, $distributor, $staff);
    }

    /**
     * Bulk enroll multiple users across multiple courses.
     * Skips pairs that already have an active inscription.
     *
     * @param  array<string>  $userIdentifications
     * @param  array<int>  $courseIds
     * @return array{inscriptions: Collection, errors: array<array<string, mixed>>}
     */
    public function enrollBulk(
        array $userIdentifications,
        array $courseIds,
        Enterprise $enterprise,
        Distributor $distributor,
        User $staff
    ): array {
        $inscriptions = collect();
        $errors = [];

        foreach ($userIdentifications as $identification) {
            $user = User::identification($identification);

            foreach ($courseIds as $courseId) {
                $course = Course::id($courseId);

                $existing = Inscription::existingInscription($user->id, $course->id)->first();

                if ($existing) {
                    $errors[] = [
                        'enterprise_enroll' => $enterprise->id,
                        'course_enroll' => $course->id,
                        'customer_name' => $user->firstname.' '.$user->lastname,
                        'customer_enroll' => $user->id,
                        'customer_identification' => $user->identification,
                        'course' => $course->title,
                        'enroll_start' => $existing->enroll_start,
                        'enroll_expire' => $existing->enroll_expire,
                        'message' => 'El usuario ya tiene una orden registrada en el periodo especificado.',
                    ];

                    continue;
                }

                $inscriptions->push($this->enroll($user, $course, $enterprise, $distributor, $staff));
            }
        }

        return compact('inscriptions', 'errors');
    }

    /**
     * Enroll a user into a course via the simple enterprise includes flow.
     * Creates Order + Inscription with zero-cost condition/method (no OrderItem, no OrderActivity).
     */
    public function enrollSimple(User $user, Course $course): Inscription
    {
        $condition = InvoiceCondition::slug('pagada');
        $method = Method::slug('acuerdo');
        $now = Carbon::now()->setTimezone(self::TIMEZONE);

        $order = new Order;
        $order->slack = $this->generateSlack('orders');
        $order->subtotal = 0;
        $order->discount = 0;
        $order->total = 0;
        $order->transaction = null;
        $order->condition_id = $condition->id;
        $order->method_id = $method->id;
        $order->course_id = $course->id;
        $order->user_id = $user->id;
        $order->enroll_start = $now;
        $order->enroll_expire = (clone $now)->addMonths(3);
        $order->payment_at = $now;

        $inscription = new Inscription;
        $inscription->user_id = $user->id;
        $inscription->course_id = $course->id;
        $inscription->culminated = 0;
        $inscription->culminated_at = null;

        return DB::transaction(function () use ($order, $inscription) {
            $order->save();
            $inscription->order_id = $order->id;
            $inscription->save();

            return $inscription;
        });
    }

    /**
     * Bulk enroll via the simple flow (no tariff, no OrderActivity).
     *
     * @param  array<string>  $userIdentifications
     * @return Collection<int, Inscription>
     */
    public function enrollSimpleBulk(array $userIdentifications, Course $course): Collection
    {
        $inscriptions = collect();

        foreach ($userIdentifications as $identification) {
            $user = User::identification($identification);
            $inscriptions->push($this->enrollSimple($user, $course));
        }

        return $inscriptions;
    }

    private function generateSlack(string $table): string
    {
        do {
            $slack = Str::random(6);
            $exists = DB::table($table)->where('slack', $slack)->exists();
        } while ($exists);

        return $slack;
    }

    private function generateNumber(string $table): int
    {
        $lastId = DB::table($table)->max('id');

        return $lastId ? $lastId + 1 : 1;
    }
}
