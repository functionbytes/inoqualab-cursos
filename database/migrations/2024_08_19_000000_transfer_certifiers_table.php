<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TransferCertifiersTables extends Migration
{
    public function up()
    {

        $certifiers = DB::connection('mysql_second')->table('certifiers')->get();
        foreach ($certifiers as $certifier) {
            DB::connection('mysql')->table('certifiers')->insert((array) $certifier);
        }

    }
}
