<?php

namespace App\Services\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait GeneratesSlackAndNumber
{
    /**
     * Generate a unique 6-character slack string for the given model class.
     * Keeps generating until a value not already present in the table is found.
     *
     * @param  class-string<Model>  $modelClass
     */
    protected function generateSlack(string $modelClass): string
    {
        $table = (new $modelClass)->getTable();

        do {
            $slack = Str::random(6);
            $exists = DB::table($table)->where('slack', $slack)->exists();
        } while ($exists);

        return $slack;
    }

    /**
     * Generate the next correlative number for the given model class.
     * Returns max(id) + 1, or 1 if the table is empty.
     *
     * @param  class-string<Model>  $modelClass
     */
    protected function generateNumber(string $modelClass): int
    {
        $table = (new $modelClass)->getTable();

        // lockForUpdate serializa la asignación del número entre transacciones
        // concurrentes (mismo patrón ya usado en CheckoutController::generate()
        // para evitar números duplicados bajo carga) -- solo tiene efecto si se
        // llama DENTRO de una transacción abierta, como hacen los callers de
        // este trait.
        $lastId = DB::table($table)->lockForUpdate()->max('id');

        return $lastId ? $lastId + 1 : 1;
    }
}
