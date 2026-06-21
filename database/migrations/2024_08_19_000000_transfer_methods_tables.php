<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TransferMethodsTables extends Migration
{
    public function up()
    {
        if (app()->environment('testing')) {
            return;
        }

        $methods = DB::connection('mysql_second')->table('methods')->get();
        foreach ($methods as $method) {
            DB::connection('mysql')->table('methods')->insert((array) $method);
        }

    }
}
