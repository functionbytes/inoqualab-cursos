<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableGroupsUsers extends Migration
{
    public function up()
    {
        Schema::create('groups_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('groups_id')->unsigned();
            $table->unsignedBigInteger('users_id')->unsigned();
            $table->foreign('groups_id')->references('id')->on('groups')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('users_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('groups_users');
    }
}
