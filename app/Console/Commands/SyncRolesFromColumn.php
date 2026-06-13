<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

/**
 * Backfill idempotente: asigna a cada usuario su rol Spatie a partir del
 * valor actual de su columna `role`. Se ejecuta una vez tras sembrar los
 * roles, y puede re-ejecutarse sin efectos secundarios.
 */
class SyncRolesFromColumn extends Command
{
    protected $signature = 'roles:sync-from-column';

    protected $description = 'Asigna roles Spatie a los usuarios según su columna role (coexistencia)';

    public function handle(): int
    {
        $validRoles = Role::where('guard_name', 'web')->pluck('name')->all();

        if (empty($validRoles)) {
            $this->error('No hay roles sembrados. Ejecuta primero db:seed --class=RolesAndPermissionsSeeder.');

            return self::FAILURE;
        }

        $count = 0;

        User::query()
            ->whereIn('role', $validRoles)
            ->chunkById(500, function ($users) use (&$count) {
                foreach ($users as $user) {
                    $user->syncRoles([$user->role]);
                    $count++;
                }
            });

        $this->info("Sincronizados {$count} usuarios con su rol Spatie.");

        return self::SUCCESS;
    }
}
