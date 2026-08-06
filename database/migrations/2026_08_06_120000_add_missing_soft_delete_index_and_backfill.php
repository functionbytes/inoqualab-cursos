<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `inscriptions`, `bundles` e `invoices` ya tenían la columna `deleted_at` en
 * la base de datos real, pero NINGUNA migración rastreada por Laravel la
 * agrega -- `php artisan migrate:status` no las lista, así que un entorno
 * nuevo/limpio corriendo solo las migraciones trackeadas se quedaría SIN esa
 * columna. Esta migración cierra el hueco de forma idempotente: no toca nada
 * en un entorno donde ya existe (como este), y la crea donde falte.
 *
 * Los modelos Inscription/Bundle/Invoice no usaban el trait SoftDeletes pese
 * a que sus tablas sí tienen la columna: Inscription::destroy() (via
 * UsersCoursesController) y Bundle::destroy() (via Managers\BundlesController)
 * hacían HARD DELETE real en vez de soft-delete -- se corrige en el mismo
 * commit agregando el trait a los 3 modelos.
 */
return new class extends Migration
{
    private function hasIndex(string $table, string $indexName): bool
    {
        return count(DB::select(
            'SHOW INDEX FROM `'.$table.'` WHERE Key_name = ?',
            [$indexName]
        )) > 0;
    }

    public function up(): void
    {
        foreach (['inscriptions', 'bundles', 'invoices'] as $table) {
            if (! Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->softDeletes();
                });
            }

            $indexName = $table.'_deleted_at_index';
            if (! $this->hasIndex($table, $indexName)) {
                Schema::table($table, function (Blueprint $t) use ($indexName) {
                    $t->index('deleted_at', $indexName);
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['inscriptions', 'bundles', 'invoices'] as $table) {
            $indexName = $table.'_deleted_at_index';
            if ($this->hasIndex($table, $indexName)) {
                Schema::table($table, function (Blueprint $t) use ($indexName) {
                    $t->dropIndex($indexName);
                });
            }
        }

        // No se hace dropSoftDeletes(): la columna ya existía en la BD real
        // antes de esta migración (schema drift), así que el rollback no
        // debe borrarla -- solo revierte lo que esta migración creó (el índice).
    }
};
