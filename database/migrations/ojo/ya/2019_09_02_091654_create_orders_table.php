<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('slack', 30)->unique();
            $table->string('number', 30)->unique();
            $table->string('reference', 30)->unique();
            $table->unsignedBigInteger('distributor_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('type_id');
            $table->unsignedBigInteger('method_id');
            $table->unsignedBigInteger('condition_id');
            $table->unsignedBigInteger('coupon_id');
            $table->text('transaction', 50)->nullable();
            $table->text('notes', 50)->nullable();
            $table->decimal('total_discount_amount', 13, 2)->default(0);
            $table->decimal('total_after_discount', 13, 2)->default(0);
            $table->decimal('total_tax_amount', 13, 2)->default(0);
            $table->decimal('total_order_amount', 13, 2)->default(0);
            $table->timestamp('payment_at');
            $table->timestamps();
            $table->index(['distributor_id', 'user_id', 'method_id', 'condition_id']);
            $table->foreign('distributor_id')->references('id')->on('distributors')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('type_id')->references('id')->on('order_type')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('method_id')->references('id')->on('order_payment')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('condition_id')->references('id')->on('order_condition')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('cascade')->onUpdate('cascade');

        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
