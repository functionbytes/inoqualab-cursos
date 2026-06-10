<?php

namespace App\Console\Commands\Cities;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Deduplicate extends Command
{
    protected $signature = 'cities:deduplicate {--dry-run : Solo muestra lo que haría, sin modificar}';

    protected $description = 'Deduplica ciudades (mismo título + estado): conserva la de menor id, repunta users.citie_id y elimina las repetidas.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        // Grupos con título + state_id repetidos.
        $groups = DB::table('cities')
            ->select('title', 'state_id', DB::raw('COUNT(*) as total'), DB::raw('MIN(id) as keep_id'))
            ->groupBy('title', 'state_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($groups->isEmpty()) {
            $this->info('No hay ciudades duplicadas. Nada que hacer.');

            return self::SUCCESS;
        }

        $this->info(($dryRun ? '[DRY-RUN] ' : '')."Grupos duplicados: {$groups->count()}");

        $totalDuplicates = 0;
        $totalUsersRepointed = 0;

        foreach ($groups as $group) {
            // Ids duplicados del grupo (todos menos el que conservamos).
            $dupIds = DB::table('cities')
                ->where('title', $group->title)
                ->where('state_id', $group->state_id)
                ->where('id', '!=', $group->keep_id)
                ->pluck('id')
                ->all();

            if (empty($dupIds)) {
                continue;
            }

            $usersAffected = DB::table('users')->whereIn('citie_id', $dupIds)->count();

            $this->line(sprintf(
                '  %s (state %s): conservar #%d, eliminar [%s]%s',
                $group->title,
                $group->state_id,
                $group->keep_id,
                implode(', ', $dupIds),
                $usersAffected ? " · repunta {$usersAffected} usuario(s)" : ''
            ));

            $totalDuplicates += count($dupIds);
            $totalUsersRepointed += $usersAffected;

            if (! $dryRun) {
                DB::transaction(function () use ($dupIds, $group) {
                    DB::table('users')->whereIn('citie_id', $dupIds)->update(['citie_id' => $group->keep_id]);
                    DB::table('cities')->whereIn('id', $dupIds)->delete();
                });
            }
        }

        $resumen = "Ciudades duplicadas {$totalDuplicates}, usuarios repuntados {$totalUsersRepointed}.";

        if ($dryRun) {
            $this->warn("[DRY-RUN] No se modificó nada. {$resumen}");
        } else {
            $this->info("Listo. {$resumen}");
            Log::info('cities:deduplicate', ['removed' => $totalDuplicates, 'users_repointed' => $totalUsersRepointed]);
        }

        return self::SUCCESS;
    }
}
