<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDistributorOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('distributor_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('slack', 30)->unique();
            $table->unsignedBigInteger('distributor_id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('enterprise_id');
            $table->tinyInteger('invoiced')->default(0);
            $table->timestamp('invoiced_at')->nullable(); // Fecha de facturación
            $table->timestamps();
            $table->index(['order_id', 'distributor_id', 'course_id', 'enterprise_id'], 'distributor_orders_indexes');
            $table->foreign('distributor_id')->references('id')->on('distributors')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('enterprise_id')->references('id')->on('enterprises')->onDelete('cascade')->onUpdate('cascade');

        });
    }

    public function down()
    {
        Schema::dropIfExists('distributor_orders');
    }
}
