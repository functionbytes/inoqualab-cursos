<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderItemsTable extends Migration
{
    public function up()
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('slack', 30)->unique();
            $table->unsignedBigInteger('order_id');
            $table->string('item_type', 30);
            $table->integer('item_id');
            $table->decimal('quantity', 8, 2)->default(0);
            $table->decimal('amount', 13, 2)->default(0);
            $table->timestamps();
            $table->index(['order_id', 'item_type'], 'orders_items_indexes');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade')->onUpdate('cascade');

        });
    }

    public function down()
    {
        Schema::dropIfExists('order_items');
    }
}
