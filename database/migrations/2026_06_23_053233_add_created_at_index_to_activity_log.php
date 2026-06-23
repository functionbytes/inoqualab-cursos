<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Índice en activity_log.created_at (tabla con ~1.5M filas).
 *
 * El visor de auditoría ordena por `created_at DESC` y filtra por rango de fechas;
 * sin índice eso es un filesort/scan sobre 1.5M filas. El comando `activitylog:clean`
 * también borra por `created_at <`. Migración aditiva e idempotente.
 */
return new class extends Migration
{
    private string $table = 'activity_log';

    private string $name = 'activity_log_created_at_index';

    public function up(): void
    {
        if ($this->indexExists($this->name)) {
            return;
        }

        Schema::table($this->table, function (Blueprint $table) {
            $table->index('created_at', $this->name);
        });
    }

    public function down(): void
    {
        if (! $this->indexExists($this->name)) {
            return;
        }

        Schema::table($this->table, function (Blueprint $table) {
            $table->dropIndex($this->name);
        });
    }

    private function indexExists(string $name): bool
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return false;
        }

        return DB::table('information_schema.statistics')
            ->whereRaw('table_schema = DATABASE()')
            ->where('table_name', $this->table)
            ->where('index_name', $name)
            ->exists();
    }
};
