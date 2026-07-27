<?php

namespace Tests\Feature\Console;

use App\Models\Course\Course;
use App\Models\Course\CourseChapter;
use App\Models\Inscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * course_progress no tiene índice único sobre (inscription_id, lesson_id), así
 * que una misma lección puede registrarse varias veces para la misma matrícula.
 * En producción eso ha dejado 413k filas sobrantes de 598k — el 69% de la tabla —
 * y porcentajes de avance imposibles, como una inscripción al 531,25 %.
 */
class DeduplicateProgressTest extends TestCase
{
    use RefreshDatabase;

    private Inscription $inscripcion;

    private array $lecciones = [];

    protected function setUp(): void
    {
        parent::setUp();

        $curso = Course::factory()->create();
        $capitulo = CourseChapter::factory()->create(['course_id' => $curso->id]);
        $usuario = User::factory()->create(['role' => 'customer']);

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

        $this->assertSame(5, DB::table('course_progress')->count(), 'La simulación borró filas.');
    }

    public function test_apply_keeps_one_row_per_lesson(): void
    {
        $this->registrarProgreso($this->lecciones[0], 3);
        $this->registrarProgreso($this->lecciones[1], 2);
        $this->registrarProgreso($this->lecciones[2], 1);

        $this->artisan('courses:deduplicate-progress --apply')->assertSuccessful();

        $this->assertSame(3, DB::table('course_progress')->count());

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
        $primero = DB::table('course_progress')->min('id');

        $this->artisan('courses:deduplicate-progress --apply')->assertSuccessful();

        $this->assertSame($primero, DB::table('course_progress')->min('id'));
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

    public function test_it_does_nothing_when_there_is_no_duplication(): void
    {
        foreach ($this->lecciones as $leccion) {
            $this->registrarProgreso($leccion, 1);
        }

        $this->artisan('courses:deduplicate-progress --apply')
            ->expectsOutputToContain('Nada que limpiar')
            ->assertSuccessful();

        $this->assertSame(4, DB::table('course_progress')->count());
    }
}
