<?php

namespace Tests\Feature\Inscriptions;

use App\Http\Controllers\Controller;
use App\Models\Course\Course;
use App\Models\Inscription;
use App\Models\Order\Order;
use App\Models\Order\OrderCondition;
use App\Models\Order\OrderMethod;
use App\Models\Order\OrderType;
use App\Models\User;
use App\Models\Users\Certificate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Blinda el helper compartido de reinscripción usado por los flujos administrativos:
 * si el alumno ya tiene inscripción se reutiliza y se renueva el certificado (no se
 * duplica); si no, se crea una nueva.
 */
class RenewOrCreateInscriptionTest extends TestCase
{
    use RefreshDatabase;

    /** Controlador anónimo que expone el método protegido. */
    private function controller(): Controller
    {
        return new class extends Controller
        {
            public function callRenew(int $u, int $c, int $o, Carbon $now): array
            {
                return $this->renewOrCreateInscription($u, $c, $o, $now);
            }
        };
    }

    private function makeOrder(int $userId): Order
    {
        $type = OrderType::firstOrCreate(['slug' => 'services'], ['slack' => 'ot', 'title' => 'Servicios', 'slug' => 'services']);
        $method = OrderMethod::firstOrCreate(['slug' => 'credit'], ['slack' => 'om', 'title' => 'Crédito', 'slug' => 'credit']);
        $condition = OrderCondition::firstOrCreate(['slug' => 'payment'], ['slack' => 'oc', 'title' => 'Pagada', 'slug' => 'payment']);

        return Order::create([
            'slack' => Str::random(6),
            'number' => random_int(1000, 999999),
            'reference' => 'FAC'.random_int(1000, 999999),
            'user_id' => $userId,
            'type_id' => $type->id,
            'method_id' => $method->id,
            'condition_id' => $condition->id,
            'total_before_discount' => 1000,
            'total_discount_amount' => 0,
            'total_tax_amount' => 0,
            'total_order_amount' => 1000,
        ]);
    }

    public function test_reuses_inscription_and_renews_certificate(): void
    {
        $now = Carbon::now();
        $user = User::factory()->create();
        $course = Course::factory()->create();

        $oldOrder = $this->makeOrder($user->id);
        $existing = Inscription::create([
            'slack' => Str::random(6),
            'order_id' => $oldOrder->id,
            'user_id' => $user->id,
            'course_id' => $course->id,
            'percent' => 40,
            'expire' => 1,
            'enroll_start' => $now->copy()->subMonths(6),
            'enroll_expire' => $now->copy()->subMonths(3),
            'culminated' => 0,
        ]);

        $futureEnd = $now->copy()->addDays(100);
        $certificate = Certificate::create([
            'slack' => Str::random(6),
            'user_id' => $user->id,
            'course_id' => $course->id,
            'inscription_id' => $existing->id,
            'start_at' => $now->copy()->subDays(265),
            'end_at' => $futureEnd,
        ]);

        $newOrder = $this->makeOrder($user->id);

        [$inscription, $isNew] = $this->controller()->callRenew($user->id, $course->id, $newOrder->id, $now);

        $this->assertFalse($isNew);
        // No se duplicó la inscripción.
        $this->assertSame(1, Inscription::where('user_id', $user->id)->where('course_id', $course->id)->count());
        // Se reutilizó la existente, con acceso reactivado y apuntando a la orden nueva.
        $this->assertSame($existing->id, $inscription->id);
        $this->assertSame($newOrder->id, (int) $inscription->fresh()->order_id);
        $this->assertSame(0, (int) $inscription->fresh()->expire);
        // El certificado vigente se extiende un año desde su vencimiento previo.
        $this->assertSame(
            $futureEnd->copy()->addYear()->toDateString(),
            Carbon::parse($certificate->fresh()->end_at)->toDateString()
        );
    }

    public function test_creates_new_inscription_when_none_exists(): void
    {
        $now = Carbon::now();
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $order = $this->makeOrder($user->id);

        [$inscription, $isNew] = $this->controller()->callRenew($user->id, $course->id, $order->id, $now);

        $this->assertTrue($isNew);
        $this->assertSame(1, Inscription::where('user_id', $user->id)->where('course_id', $course->id)->count());
        $this->assertSame($order->id, (int) $inscription->order_id);
    }
}
