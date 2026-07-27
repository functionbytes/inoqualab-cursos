<?php

namespace Tests\Feature\Console;

use Database\Seeders\CatalogsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Los catálogos base existían en producción pero ningún seeder los creaba: una
 * base recién migrada quedaba sin condiciones de orden, métodos de pago ni
 * tipos de lección, y con eso ni el checkout ni la facturación funcionan.
 *
 * Los valores que se comprueban aquí son los que el código busca por slug o
 * por id, así que cambiarlos rompe cosas en silencio.
 */
class CatalogsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_the_slugs_the_code_looks_up(): void
    {
        $this->seed(CatalogsSeeder::class);

        // invoices:generate
        $this->assertDatabaseHas('invoice_condition', ['slug' => 'generada']);
        $this->assertDatabaseHas('invoice_method', ['slug' => 'credit']);

        // inscripciones masivas y checkout
        $this->assertDatabaseHas('order_condition', ['slug' => 'payment']);
        $this->assertDatabaseHas('order_type', ['slug' => 'services']);
        $this->assertDatabaseHas('order_method', ['slug' => 'credit']);
    }

    public function test_lesson_type_ids_match_what_the_controller_maps(): void
    {
        $this->seed(CatalogsSeeder::class);

        // LessonsController mapea estos ids a colecciones de media; si cambian,
        // los adjuntos van a la colección equivocada sin avisar.
        $esperado = [1 => 'VIDEO', 2 => 'AUDIO', 3 => 'IMAGEN', 4 => 'ZIP', 5 => 'PDF', 6 => 'QUIZ', 7 => 'TEXTO'];

        foreach ($esperado as $id => $titulo) {
            $this->assertSame(
                $titulo,
                DB::table('course_types')->where('id', $id)->value('title'),
                "course_types id={$id} debería ser {$titulo}"
            );
        }
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seed(CatalogsSeeder::class);
        $primera = DB::table('order_method')->count();

        $this->seed(CatalogsSeeder::class);

        $this->assertSame($primera, DB::table('order_method')->count(), 'Relanzarlo duplicó filas.');
    }

    public function test_seeder_does_not_overwrite_existing_rows(): void
    {
        // Escenario real: se relanza sobre una base que ya tiene los catálogos,
        // quizá con títulos ajustados a mano. No debe pisarlos.
        DB::table('order_method')->insert([
            'id' => 1, 'slack' => 'custom', 'title' => 'EFECTIVO EN CAJA', 'slug' => 'cash',
        ]);

        $this->seed(CatalogsSeeder::class);

        $this->assertSame('EFECTIVO EN CAJA', DB::table('order_method')->where('id', 1)->value('title'));
    }
}
