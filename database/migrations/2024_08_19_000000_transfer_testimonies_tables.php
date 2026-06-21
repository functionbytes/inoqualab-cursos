<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TransferTestimoniesTables extends Migration
{
    public function up()
    {
        if (app()->environment('testing')) {
            return;
        }

        $testimonies = DB::connection('mysql_second')->table('testimonies')->get();
        foreach ($testimonies as $testimonie) {
            DB::connection('mysql')->table('testimonies')->insert((array) $testimonie);
        }

    }
}
