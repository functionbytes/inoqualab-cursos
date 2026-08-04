<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * course_progress no tenía índice único sobre (inscription_id, lesson_id),
 * lo que permitía duplicados y disparaba porcentajes de avance imposibles
 * (una inscripción llegó a marcar 531,25 %).
 *
 * Deduplica y recalcula porcentajes por su cuenta antes de crear el índice
 * (misma lógica que app/Console/Commands/Courses/DeduplicateProgress.php,
 * pensado para correrse a mano y revisar su salida antes de un cambio así de
 * grande en producción). Repetirla aquí hace que la migración sea segura por
 * sí sola en cualquier entorno — staging, un despliegue que corra `migrate`
 * sin que nadie haya lanzado el comando primero — en vez de depender de un
 * paso manual externo no garantizado.
 *
 * Sustituye el índice no-único course_progress_inscription_id_lesson_id_index
 * (creado en 2026_06_13_120000_add_performance_indexes.php) por uno único con
 * el mismo nombre de columnas para no perder cobertura de las consultas.
 */
return new class extends Migration
{
    private function hasIndex(string $table, string $indexName): bool
    {
        $result = DB::select(
            'SHOW INDEX FROM `'.$table.'` WHERE Key_name = ?',
            [$indexName]
        );

        return count($result) > 0;
    }

    /** Conserva la fila más antigua de cada (inscription_id, lesson_id) y borra el resto. */
    private function deduplicate(): void
    {
        $grupos = DB::table('course_progress')
            ->selectRaw('inscription_id, lesson_id, MIN(id) as conservar')
            ->groupBy('inscription_id', 'lesson_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($grupos->chunk(500) as $lote) {
            DB::transaction(function () use ($lote) {
                foreach ($lote as $g) {
                    DB::table('course_progress')
                        ->where('inscription_id', $g->inscription_id)
                        ->where('lesson_id', $g->lesson_id)
                        ->where('id', '!=', $g->conservar)
                        ->delete();
                }
            });
        }
    }

    /** Recalcula el avance de las inscripciones cuyo porcentaje quedó por encima de 100 por los duplicados. */
    private function recalcularPorcentajes(): void
    {
        DB::table('inscriptions')
            ->whereRaw('CAST(percent AS DECIMAL(10,2)) > 100')
            ->orderBy('id')
            ->chunkById(200, function ($inscripciones) {
                foreach ($inscripciones as $i) {
                    $lecciones = DB::table('course_lessons')
                        ->where('course_id', $i->course_id)
                        ->whereNull('deleted_at')
                        ->count();

                    $hechas = DB::table('course_progress')
                        ->where('inscription_id', $i->id)
                        ->distinct()
                        ->count('lesson_id');

                    $percent = $lecciones > 0
                        ? min(100, round(($hechas / $lecciones) * 100, 2))
                        : 100;

                    DB::table('inscriptions')->where('id', $i->id)->update(['percent' => $percent]);
                }
            });
    }

    public function up(): void
    {
        $this->deduplicate();
        $this->recalcularPorcentajes();

        if ($this->hasIndex('course_progress', 'course_progress_inscription_id_lesson_id_index')) {
            Schema::table('course_progress', function (Blueprint $table) {
                $table->dropIndex('course_progress_inscription_id_lesson_id_index');
            });
        }

        if (! $this->hasIndex('course_progress', 'course_progress_inscription_id_lesson_id_unique')) {
            Schema::table('course_progress', function (Blueprint $table) {
                $table->unique(['inscription_id', 'lesson_id'], 'course_progress_inscription_id_lesson_id_unique');
            });
        }
    }

    public function down(): void
    {
        if ($this->hasIndex('course_progress', 'course_progress_inscription_id_lesson_id_unique')) {
            Schema::table('course_progress', function (Blueprint $table) {
                $table->dropUnique('course_progress_inscription_id_lesson_id_unique');
            });
        }

        if (! $this->hasIndex('course_progress', 'course_progress_inscription_id_lesson_id_index')) {
            Schema::table('course_progress', function (Blueprint $table) {
                $table->index(['inscription_id', 'lesson_id'], 'course_progress_inscription_id_lesson_id_index');
            });
        }
    }
};
