<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceConditionTable extends Migration
{
    public function up()
    {
        Schema::create('invoice_condition', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('slack', 30)->unique();
            $table->string('label', 250);
            $table->string('slug', 250);
            $table->tinyInteger('available')->default(1);
            $table->timestamps();
        });

    }

    public function down()
    {
        Schema::dropIfExists('invoice_condition');
    }
}
