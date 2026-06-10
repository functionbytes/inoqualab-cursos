<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInscriptionsTable extends Migration
{
    public function up()
    {
        Schema::create('inscriptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->date('enroll_start')->nullable();
            $table->date('enroll_expire')->nullable();
            $table->date('enroll_culminated')->nullable();
            $table->string('percent')->nullable();
            $table->tinyInteger('culminated')->default(1);
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade')->onUpdate('cascade');
            $table->index(['user_id', 'course_id', 'order_id', 'percent', 'culminated']);

        });
    }

    public function down()
    {
        Schema::dropIfExists('inscriptions');
    }
}
