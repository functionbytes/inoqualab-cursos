<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentGatewaysTable extends Migration
{
    public function up()
    {
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('identifier');
            $table->string('currency');
            $table->string('title');
            $table->text('description');
            $table->text('keys');
            $table->text('modal_name');
            $table->tinyInteger('enabled_test')->nullable()->default(0);
            $table->tinyInteger('available')->nullable()->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_gateways');
    }
}
