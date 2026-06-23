<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Índice compuesto (method_id, condition_id) en orders (44k filas).
 *
 * Motivo: los dashboards (manager/support/accounting) cuentan órdenes con
 * `Order::where('method_id',1)->where('condition_id',4)->count()` en 4+ sitios.
 * Hoy solo existen índices de columna única por separado; el compuesto deja
 * resolver ambas condiciones desde el índice (covering para el COUNT).
 * Migración aditiva e idempotente.
 */
return new class extends Migration
{
    private string $table = 'orders';

    private string $name = 'orders_method_id_condition_id_index';

    public function up(): void
    {
        if ($this->indexExists($this->name)) {
            return;
        }

        Schema::table($this->table, function (Blueprint $table) {
            $table->index(['method_id', 'condition_id'], $this->name);
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
