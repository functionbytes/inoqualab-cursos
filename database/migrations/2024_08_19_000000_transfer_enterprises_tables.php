<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TransferEnterprisesTables extends Migration
{
    public function up()
    {

        $users = DB::connection('mysql_second')->table('enterprise_user')->get();
        dd($users);
        foreach ($users as $user) {
            DB::connection('mysql')->table('enterprise_user')->insert((array) $user);
        }

    }
}
