<?php

namespace App\Console\Commands\Supports;

use App\Models\Setting\Setting;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoNotificationdeletes extends Command
{
    protected $signature = 'notification:autodelete';

    protected $description = 'Elimina notificaciones leídas más antiguas que N días según configuración.';

    public function handle(): int
    {
        $cronset = Setting::where('key', 'cronjob_set')->first();
        if ($cronset) {
            $cronset->updated_at = now();
            $cronset->save();
        }

        if (setting('AUTO_NOTIFICATION_DELETE_ENABLE') != 'on') {
            $this->info('Auto-eliminación de notificaciones desactivada.');

            return self::SUCCESS;
        }

        $days = (int) setting('AUTO_NOTIFICATION_DELETE_DAYS') ?: 30;
        $deleted = 0;

        // withTrashed(): User usa SoftDeletes, así que sin esto el scope
        // global excluye usuarios eliminados y sus notificaciones nunca se
        // purgaban -- se acumulaban indefinidamente en la tabla.
        User::withTrashed()->with('notifications')->chunk(200, function ($users) use ($days, &$deleted) {
            foreach ($users as $user) {
                foreach ($user->notifications as $notification) {
                    if ($notification->read_at && $notification->read_at->addDays($days)->lte(now())) {
                        $notification->delete();
                        $deleted++;
                    }
                }
            }
        });

        $this->info("Notificaciones eliminadas: {$deleted}.");
        Log::info('notification:autodelete', ['deleted' => $deleted, 'days' => $days]);

        return self::SUCCESS;
    }
}
