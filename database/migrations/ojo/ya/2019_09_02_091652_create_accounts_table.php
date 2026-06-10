<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountsTable extends Migration
{
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('slack', 30)->unique();
            $table->unsignedBigInteger('distributor_id');
            $table->unsignedBigInteger('type_id');
            $table->text('description')->nullable();
            $table->tinyInteger('available')->default(1);
            $table->index(['distributor_id', 'type_id', 'available']);
            $table->timestamps();
            $table->foreign('distributor_id')->references('id')->on('distributors')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('type_id')->references('id')->on('account_type')->onDelete('cascade')->onUpdate('cascade');
        });

    }

    public function down()
    {
        Schema::dropIfExists('accounts');
    }
}
