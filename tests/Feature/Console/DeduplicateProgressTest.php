<?php

namespace Tests\Feature\Console;

use App\Models\Course\Course;
use App\Models\Course\CourseChapter;
use App\Models\Inscription;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * course_progress ahora tiene índice único sobre (inscription_id, lesson_id)
 * — ver migración 2026_08_04_120000_make_course_progress_inscription_lesson_unique
 * — que es justo lo que impedía que se volvieran a acumular los duplicados que
 * este comando limpia (llegó a haber 413k filas sobrantes de 598k, el 69% de la
 * tabla, y una inscripción marcada al 531,25 %).
 *
 * Como el índice ya bloquea los duplicados a nivel de base de datos, para poder
 * seguir probando la lógica de limpieza hay que quitarlo temporalmente y simular
 * el estado "legado" en el que corrió el comando la primera vez. El ALTER TABLE
 * hace commit implícito en MySQL, así que esta clase no confía en el rollback
 * transaccional de RefreshDatabase para su propia data: limpia explícitamente
 * en tearDown() y repone el índice para no afectar a otros tests.
 */
class DeduplicateProgressTest extends TestCase
{
    use RefreshDatabase;

    private const INDICE = 'course_progress_inscription_id_lesson_id_unique';

    private Inscription $inscripcion;

    private array $lecciones = [];

    private int $capituloId;

    private int $cursoId;

    private int $usuarioId;

    protected function setUp(): void
    {
        parent::setUp();

        $curso = Course::factory()->create();
        $capitulo = CourseChapter::factory()->create(['course_id' => $curso->id]);
        $usuario = User::factory()->create(['role' => 'customer']);

        $this->cursoId = $curso->id;
        $this->capituloId = $capitulo->id;
        $this->usuarioId = $usuario->id;

        DB::table('course_types')->insertOrIgnore([['id' => 7, 'title' => 'TEXTO', 'slug' => 'texto']]);

        foreach (range(1, 4) as $i) {
            $this->lecciones[] = DB::table('course_lessons')->insertGetId([
                'slack' => Str::random(10), 'title' => "LECCION {$i}", 'available' => 1,
                'position' => $i, 'type_id' => 7, 'course_id' => $curso->id,
                'chapter_id' => $capitulo->id, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        $this->inscripcion = Inscription::factory()->create([
            'user_id' => $usuario->id,
            'course_id' => $curso->id,
        ]);

        // Simula el estado legado (sin índice) para poder sembrar duplicados.
        // Hace commit implícito: por eso la limpieza en tearDown() es manual.
        Schema::table('course_progress', function (Blueprint $table) {
            $table->dropUnique(self::INDICE);
        });
    }

    protected function tearDown(): void
    {
        DB::table('course_progress')->where('inscription_id', $this->inscripcion->id)->delete();
        DB::table('inscriptions')->where('id', $this->inscripcion->id)->delete();
        DB::table('course_lessons')->whereIn('id', $this->lecciones)->delete();
        DB::table('course_chapters')->where('id', $this->capituloId)->delete();
        DB::table('courses')->where('id', $this->cursoId)->delete();
        DB::table('users')->where('id', $this->usuarioId)->delete();

        if (! $this->tieneIndiceUnico()) {
            Schema::table('course_progress', function (Blueprint $table) {
                $table->unique(['inscription_id', 'lesson_id'], self::INDICE);
            });
        }

        parent::tearDown();
    }

    private function tieneIndiceUnico(): bool
    {
        return count(DB::select(
            'SHOW INDEX FROM `course_progress` WHERE Key_name = ?',
            [self::INDICE]
        )) > 0;
    }

    private function registrarProgreso(int $leccion, int $veces = 1): void
    {
        foreach (range(1, $veces) as $_) {
            DB::table('course_progress')->insert([
                'user_id' => $this->inscripcion->user_id,
                'course_id' => $this->inscripcion->course_id,
                'inscription_id' => $this->inscripcion->id,
                'lesson_id' => $leccion,
                'culminated' => 1,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function test_dry_run_reports_without_deleting(): void
    {
        $this->registrarProgreso($this->lecciones[0], 3);
        $this->registrarProgreso($this->lecciones[1], 2);

        $this->artisan('courses:deduplicate-progress')
            ->expectsOutputToContain('filas a eliminar')
            ->assertSuccessful();

        $this->assertSame(5, DB::table('course_progress')->where('inscription_id', $this->inscripcion->id)->count(), 'La simulación borró filas.');
    }

    public function test_apply_keeps_one_row_per_lesson(): void
    {
        $this->registrarProgreso($this->lecciones[0], 3);
        $this->registrarProgreso($this->lecciones[1], 2);
        $this->registrarProgreso($this->lecciones[2], 1);

        $this->artisan('courses:deduplicate-progress --apply')->assertSuccessful();

        $this->assertSame(3, DB::table('course_progress')->where('inscription_id', $this->inscripcion->id)->count());

        foreach (array_slice($this->lecciones, 0, 3) as $leccion) {
            $this->assertSame(
                1,
                DB::table('course_progress')->where('lesson_id', $leccion)->count(),
                'Debe quedar exactamente un registro por lección.'
            );
        }
    }

    public function test_it_keeps_the_oldest_row(): void
    {
        // El primer registro es el que dice cuándo completó la lección de verdad.
        $this->registrarProgreso($this->lecciones[0], 3);
        $primero = DB::table('course_progress')->where('inscription_id', $this->inscripcion->id)->min('id');

        $this->artisan('courses:deduplicate-progress --apply')->assertSuccessful();

        $this->assertSame($primero, DB::table('course_progress')->where('inscription_id', $this->inscripcion->id)->min('id'));
    }

    public function test_it_recalculates_percentages_over_one_hundred(): void
    {
        // 4 lecciones, 3 completadas pero con duplicados: el cálculo ingenuo
        // daba (8 filas / 4 lecciones) = 200 %.
        $this->registrarProgreso($this->lecciones[0], 4);
        $this->registrarProgreso($this->lecciones[1], 3);
        $this->registrarProgreso($this->lecciones[2], 1);

        DB::table('inscriptions')->where('id', $this->inscripcion->id)->update(['percent' => '200']);

        $this->artisan('courses:deduplicate-progress --apply')->assertSuccessful();

        $percent = (float) DB::table('inscriptions')->where('id', $this->inscripcion->id)->value('percent');

        $this->assertSame(75.0, $percent, '3 de 4 lecciones son el 75 %.');
    }

    /**
     * Regresión: antes solo se recalculaba percent cuando quedaba > 100.
     * Una inscripción con duplicados puede quedar inflada SIN pasar de 100
     * (aquí: 2 de 4 lecciones reales = 50 %, pero un percent legado de 75 %
     * nunca se corregía porque 75 <= 100) y eso nunca se detectaba.
     */
    public function test_it_recalculates_inflated_percentages_that_stay_under_one_hundred(): void
    {
        // Solo 2 lecciones completadas de 4 (con duplicados) = 50 % real.
        $this->registrarProgreso($this->lecciones[0], 3);
        $this->registrarProgreso($this->lecciones[1], 2);

        // percent legado, inflado por los duplicados, pero sin pasar de 100.
        DB::table('inscriptions')->where('id', $this->inscripcion->id)->update(['percent' => '75']);

        $this->artisan('courses:deduplicate-progress --apply')->assertSuccessful();

        $percent = (float) DB::table('inscriptions')->where('id', $this->inscripcion->id)->value('percent');

        $this->assertSame(50.0, $percent, '2 de 4 lecciones son el 50 %, no el 75 % legado.');
    }

    public function test_it_does_nothing_when_there_is_no_duplication(): void
    {
        foreach ($this->lecciones as $leccion) {
            $this->registrarProgreso($leccion, 1);
        }

        $this->artisan('courses:deduplicate-progress --apply')
            ->expectsOutputToContain('Nada que limpiar')
            ->assertSuccessful();

        $this->assertSame(4, DB::table('course_progress')->where('inscription_id', $this->inscripcion->id)->count());
    }

    /**
     * Regresión del propio fix: una vez repuesto el índice único (en tearDown de
     * los demás tests), un segundo insert para la misma (inscription_id,
     * lesson_id) debe rechazarse a nivel de base de datos, no solo del ORM.
     */
    public function test_unique_index_blocks_new_duplicates_once_restored(): void
    {
        Schema::table('course_progress', function (Blueprint $table) {
            $table->unique(['inscription_id', 'lesson_id'], self::INDICE);
        });

        $this->registrarProgreso($this->lecciones[0], 1);

        $this->expectException(UniqueConstraintViolationException::class);

        $this->registrarProgreso($this->lecciones[0], 1);
    }
}
