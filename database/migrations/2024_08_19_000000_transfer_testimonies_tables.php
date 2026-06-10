<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TransferTestimoniesTables extends Migration
{
    public function up()
    {

        $testimonies = DB::connection('mysql_second')->table('testimonies')->get();
        foreach ($testimonies as $testimonie) {
            DB::connection('mysql')->table('testimonies')->insert((array) $testimonie);
        }

    }
}
