<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountTypeTable extends Migration
{
    public function up()
    {
        Schema::create('account_type', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('slack', 30)->unique();
            $table->string('title', 250);
            $table->text('description')->nullable();
            $table->tinyInteger('available')->default(1);
            $table->timestamps();
            $table->index(['available']);
        });

    }

    public function down()
    {
        Schema::dropIfExists('master_account_type');
    }
}
