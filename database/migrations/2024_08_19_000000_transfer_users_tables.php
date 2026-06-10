<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TransferUsersTables extends Migration
{
    public function up()
    {
        // Copiar la tabla 'courses'
        $courses = DB::connection('mysql_second')->table('users')->get();
        foreach ($courses as $course) {
            DB::connection('mysql')->table('users')->insert((array) $course);
        }

    }
}
