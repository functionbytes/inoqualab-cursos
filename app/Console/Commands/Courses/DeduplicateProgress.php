<?php

namespace App\Console\Commands\Courses;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Elimina el progreso duplicado de course_progress.
 *
 * La tabla no tiene índice único sobre (inscription_id, lesson_id), así que
 * una misma lección puede quedar registrada varias veces para la misma
 * matrícula. Con el tiempo eso ha dejado ~413k filas sobrantes de 598k: el 69%
 * de la tabla.
 *
 * El efecto visible es el porcentaje de avance: se calcula dividiendo el
 * progreso entre el número de lecciones, así que con duplicados pasa de 100.
 * En la base hay una inscripción marcada al 531,25 %.
 *
 * Por defecto solo informa. Hay que pasar --apply para que borre, y conviene
 * hacer copia de seguridad antes.
 */
class DeduplicateProgress extends Command
{
    protected $signature = 'courses:deduplicate-progress
                            {--apply : Ejecuta el borrado (sin esta opción solo informa)}
                            {--chunk=500 : Grupos a procesar por lote}';

    protected $description = 'Detecta y elimina registros duplicados de avance en los cursos';

    public function handle(): int
    {
        $this->info('Analizando course_progress…');

        $total = DB::table('course_progress')->count();

        // De cada grupo (inscripción, lección) se conserva el registro más
        // antiguo, que es el que refleja cuándo completó la lección de verdad.
        $grupos = DB::table('course_progress')
            ->selectRaw('inscription_id, lesson_id, COUNT(*) as veces, MIN(id) as conservar')
            ->groupBy('inscription_id', 'lesson_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        $sobrantes = $grupos->sum('veces') - $grupos->count();

        $this->newLine();
        $this->line('  filas totales      : '.number_format($total));
        $this->line('  grupos duplicados  : '.number_format($grupos->count()));
        $this->line('  filas a eliminar   : '.number_format($sobrantes));
        $this->line('  quedarían          : '.number_format($total - $sobrantes));

        $fuera = DB::table('inscriptions')->whereRaw('CAST(percent AS DECIMAL(10,2)) > 100')->count();
        if ($fuera > 0) {
            $this->newLine();
            $this->warn("  {$fuera} inscripción(es) con porcentaje mayor que 100; se recalculan al aplicar.");
        }

        if ($sobrantes === 0) {
            $this->newLine();
            $this->info('Nada que limpiar.');

            return self::SUCCESS;
        }

        if (! $this->option('apply')) {
            $this->newLine();
            $this->comment('Simulación. Vuelve a lanzarlo con --apply para ejecutar el borrado.');
            $this->comment('Haz copia de seguridad de course_progress antes.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->info('Eliminando duplicados…');

        $borradas = 0;
        $barra = $this->output->createProgressBar($grupos->count());

        foreach ($grupos->chunk((int) $this->option('chunk')) as $lote) {
            DB::transaction(function () use ($lote, &$borradas, $barra) {
                foreach ($lote as $g) {
                    $borradas += DB::table('course_progress')
                        ->where('inscription_id', $g->inscription_id)
                        ->where('lesson_id', $g->lesson_id)
                        ->where('id', '!=', $g->conservar)
                        ->delete();
                    $barra->advance();
                }
            });
        }

        $barra->finish();
        $this->newLine(2);
        $this->info('Eliminadas '.number_format($borradas).' filas.');

        $this->info('Recalculando porcentajes afectados…');
        // No basta con recalcular las que quedaron > 100: una inscripción con
        // duplicados puede haber quedado inflada pero <= 100 (p. ej. 5
        // lecciones reales de 10 mostradas como 7 por duplicados = 70%) y
        // eso nunca se corregía. Se recalculan TODAS las inscripciones que
        // tenían algún grupo duplicado, más las que quedaron > 100 por otra
        // causa.
        $inscripcionesAfectadas = $grupos->pluck('inscription_id')->unique()->values();
        $corregidas = $this->recalcularPorcentajes($inscripcionesAfectadas);
        $this->info('Corregidas '.number_format($corregidas).' inscripciones.');

        $this->newLine();
        $this->comment('Siguiente paso: añadir el índice único (inscription_id, lesson_id)');
        $this->comment('para que no vuelvan a aparecer. Ya no habrá duplicados que lo impidan.');

        return self::SUCCESS;
    }

    /**
     * Recalcula el avance de las matrículas que tenían grupos duplicados
     * (percent inflado, con o sin pasar de 100) y de cualquier otra que haya
     * quedado > 100 por una causa distinta.
     */
    private function recalcularPorcentajes(Collection $inscripcionesAfectadas): int
    {
        $corregidas = 0;

        DB::table('inscriptions')
            ->where(function ($query) use ($inscripcionesAfectadas) {
                $query->whereRaw('CAST(percent AS DECIMAL(10,2)) > 100');
                if ($inscripcionesAfectadas->isNotEmpty()) {
                    $query->orWhereIn('id', $inscripcionesAfectadas);
                }
            })
            ->orderBy('id')
            ->chunkById(200, function ($inscripciones) use (&$corregidas) {
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
                    $corregidas++;
                }
            });

        return $corregidas;
    }
}
