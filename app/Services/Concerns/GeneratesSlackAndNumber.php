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
        $lastId = DB::table($table)->max('id');

        return $lastId ? $lastId + 1 : 1;
    }
}
