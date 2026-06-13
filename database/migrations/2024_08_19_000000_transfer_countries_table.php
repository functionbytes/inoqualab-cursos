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

        $countries = DB::connection('mysql_second')->table('countries')->get();
        foreach ($countries as $countrie) {
            DB::connection('mysql')->table('countries')->insert((array) $countrie);
        }

        $states = DB::connection('mysql_second')->table('states')->get();
        foreach ($states as $state) {
            DB::connection('mysql')->table('states')->insert((array) $state);
        }
        $cities = DB::connection('mysql_second')->table('cities')->get();
        foreach ($cities as $citie) {
            DB::connection('mysql')->table('cities')->insert((array) $citie);
        }

    }
};
