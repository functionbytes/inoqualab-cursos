<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponsTable extends Migration
{
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('slack', 30)->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('code');
            $table->tinyInteger('type')->default(1);
            $table->float('amount');
            $table->float('min_price')->default(0);
            $table->string('start_date')->nullable();
            $table->string('end_date')->nullable();
            $table->integer('per_user_limit');
            $table->string('bundle_ids');
            $table->string('course_ids');
            $table->integer('per_user_limit')->default(1);
            $table->tinyInteger('available')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('coupons');
    }
}
