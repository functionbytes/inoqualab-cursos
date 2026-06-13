<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        if (app()->environment('testing')) {
            return;
        }

        $certifiers = DB::connection('mysql_second')->table('certifiers')->get();
        foreach ($certifiers as $certifier) {
            DB::connection('mysql')->table('certifiers')->insert((array) $certifier);
        }

    }
};
