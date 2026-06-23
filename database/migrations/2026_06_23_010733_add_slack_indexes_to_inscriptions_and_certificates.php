<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Añade índices sobre la columna `slack` de inscriptions (44k filas) y certificates (31k filas).
 *
 * Motivo: el scope `scopeSlack()` (Model::slack($x)) se usa ~33 veces en controllers para resolver
 * recursos por slack; sin índice cada lookup es un full table scan. Índice NO único a propósito
 * (no falla si existieran slacks duplicados en datos legados). Migración aditiva e idempotente.
 */
return new class extends Migration
{
    /**
     * @var array<int, array{table:string, column:string, name:string}>
     */
    private array $indexes = [
        ['table' => 'inscriptions', 'column' => 'slack', 'name' => 'inscriptions_slack_index'],
        ['table' => 'certificates', 'column' => 'slack', 'name' => 'certificates_slack_index'],
    ];

    public function up(): void
    {
        foreach ($this->indexes as $idx) {
            if ($this->indexExists($idx['table'], $idx['name'])) {
                continue;
            }

            Schema::table($idx['table'], function (Blueprint $table) use ($idx) {
                $table->index($idx['column'], $idx['name']);
            });
        }
    }

    public function down(): void
    {
        foreach ($this->indexes as $idx) {
            if (! $this->indexExists($idx['table'], $idx['name'])) {
                continue;
            }

            Schema::table($idx['table'], function (Blueprint $table) use ($idx) {
                $table->dropIndex($idx['name']);
            });
        }
    }

    /**
     * Comprueba si un índice ya existe (solo MySQL; en otros drivers se omite la optimización).
     */
    private function indexExists(string $table, string $name): bool
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return false;
        }

        return DB::table('information_schema.statistics')
            ->whereRaw('table_schema = DATABASE()')
            ->where('table_name', $table)
            ->where('index_name', $name)
            ->exists();
    }
};
