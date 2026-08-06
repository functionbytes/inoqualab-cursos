<?php

namespace Tests\Unit\Services;

use App\Models\Course\Course;
use App\Models\Inscription;
use App\Models\Order\Order;
use App\Models\Order\OrderCondition;
use App\Models\Order\OrderMethod;
use App\Models\Order\OrderType;
use App\Models\User;
use App\Services\InscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresión: enrollSimple() -- el único punto de entrada de matrícula
 * gratuita realmente alcanzable (includes() en Supports/Distributors, y
 * enrollSimpleBulk() en Managers) -- no tenía NINGÚN chequeo de duplicados,
 * a diferencia del método muerto enrollIfNotDuplicate() (nunca llamado desde
 * ningún controller). Un doble-click en "matricular", o repetir la misma
 * identificación dos veces en un bulk, creaba una Order+Inscription nueva
 * cada vez, sin límite.
 */
class InscriptionServiceEnrollSimpleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        OrderType::firstOrCreate(['slug' => 'services'], ['slack' => 'ot', 'title' => 'Servicios', 'slug' => 'services']);
        OrderMethod::firstOrCreate(['slug' => 'credit'], ['slack' => 'om', 'title' => 'Crédito', 'slug' => 'credit']);
        OrderCondition::firstOrCreate(['slug' => 'payment'], ['slack' => 'oc', 'title' => 'Pagada', 'slug' => 'payment']);
    }

    public function test_calling_it_twice_for_the_same_user_and_course_does_not_duplicate(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $course = Course::factory()->create();

        $first = (new InscriptionService)->enrollSimple($user, $course);
        $second = (new InscriptionService)->enrollSimple($user, $course);

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, Inscription::where('user_id', $user->id)->where('course_id', $course->id)->count());
        $this->assertSame(1, Order::where('user_id', $user->id)->count());
    }

    public function test_different_users_get_independent_enrollments(): void
    {
        $userA = User::factory()->create(['role' => 'customer']);
        $userB = User::factory()->create(['role' => 'customer']);
        $course = Course::factory()->create();

        $a = (new InscriptionService)->enrollSimple($userA, $course);
        $b = (new InscriptionService)->enrollSimple($userB, $course);

        $this->assertNotSame($a->id, $b->id);
        $this->assertSame(2, Inscription::where('course_id', $course->id)->count());
    }
}
