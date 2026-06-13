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

        $certifications = DB::connection('mysql_second')->table('certifications')->get();
        foreach ($certifications as $certification) {
            DB::connection('mysql')->table('certifications')->insert((array) $certification);
        }

    }
};
