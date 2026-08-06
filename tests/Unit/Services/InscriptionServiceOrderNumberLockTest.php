<?php

namespace Tests\Unit\Services;

use App\Models\Course\Course;
use App\Models\Order\OrderCondition;
use App\Models\Order\OrderMethod;
use App\Models\Order\OrderType;
use App\Models\User;
use App\Services\InscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Regresión: generateNumber() calculaba `max(id) + 1` SIN lockForUpdate() y,
 * en enroll()/enrollSimple(), se calculaba ANTES de que empezara la
 * transacción de guardado -- dos matrículas concurrentes podían leer el
 * mismo max(id) antes de que ninguna de las dos confirmara, y la segunda
 * violaba el unique de orders.number con un QueryException sin capturar.
 * Mismo patrón que CheckoutController::generate() ya usa correctamente para
 * el flujo principal de compra.
 *
 * PHPUnit corre en un solo proceso, así que la concurrencia real no es
 * reproducible aquí -- se verifica que el SELECT ahora sí lleva
 * "for update", que es lo que hace que el lock proteja bajo carga real.
 */
class InscriptionServiceOrderNumberLockTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        OrderType::firstOrCreate(['slug' => 'services'], ['slack' => 'ot', 'title' => 'Servicios', 'slug' => 'services']);
        OrderMethod::firstOrCreate(['slug' => 'credit'], ['slack' => 'om', 'title' => 'Crédito', 'slug' => 'credit']);
        OrderCondition::firstOrCreate(['slug' => 'payment'], ['slack' => 'oc', 'title' => 'Pagada', 'slug' => 'payment']);
    }

    public function test_enroll_simple_locks_the_orders_table_while_computing_the_number(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $course = Course::factory()->create();

        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->sql;
        });

        (new InscriptionService)->enrollSimple($user, $course);

        $lockingQueries = array_filter($queries, fn ($sql) => str_contains($sql, 'orders') && str_contains(strtolower($sql), 'for update'));

        $this->assertNotEmpty(
            $lockingQueries,
            'generateNumber() no está usando lockForUpdate() sobre orders -- dos matrículas concurrentes podrían calcular el mismo número.'
        );
    }
}
