<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TransferCertificationsTables extends Migration
{
    public function up()
    {

        $certifications = DB::connection('mysql_second')->table('certifications')->get();
        foreach ($certifications as $certification) {
            DB::connection('mysql')->table('certifications')->insert((array) $certification);
        }

    }
}
