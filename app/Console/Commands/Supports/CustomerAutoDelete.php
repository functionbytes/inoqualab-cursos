<?php

namespace App\Console\Commands\Supports;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CustomerAutoDelete extends Command
{
    protected $signature = 'customers:inactive_delete';

    protected $description = 'Comando desactivado — la lógica de Customer separado no aplica a este proyecto.';

    public function handle(): int
    {
        // Este comando fue construido para un scaffold con modelo Customer independiente
        // que no existe en este proyecto. Todos los usuarios están en el modelo User.
        // El comando se mantiene registrado para no romper el schedule, pero no hace nada.
        Log::info('customers:inactive_delete — comando desactivado (modelo Customer no existe en este proyecto).');
        $this->info('Comando desactivado. Ver CustomerAutoDelete.php para más información.');

        return self::SUCCESS;
    }
}
