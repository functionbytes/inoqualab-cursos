<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDistributorTransactionsTable extends Migration
{
    public function up()
    {
        Schema::create('distributor_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('slack', 30)->unique();
            $table->unsignedBigInteger('distributor_id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('account_id');
            $table->integer('account_type');
            $table->text('notes')->nullable();
            $table->date('transaction_date')->nullable();
            $table->timestamps();
            $table->index(['distributor_id', 'order_id', 'account_id', 'account_type'], 'transaction_indexes');
            $table->foreign('distributor_id')->references('id')->on('distributors')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade')->onUpdate('cascade');

        });
    }

    public function down()
    {
        Schema::dropIfExists('distributor_transactions');
    }
}
