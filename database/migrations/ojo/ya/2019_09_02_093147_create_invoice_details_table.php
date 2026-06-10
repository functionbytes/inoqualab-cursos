<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceDetailsTable extends Migration
{
    public function up()
    {
        Schema::create('invoice_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('slack', 30)->unique();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('enterprise_id');
            $table->decimal('quantity', 8, 2)->default(0);
            $table->decimal('amount', 13, 2)->default(0);
            $table->timestamps();
            $table->index(['order_id', 'course_id'], 'invoices_details_indexes');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('enterprise_id')->references('id')->on('enterprises')->onDelete('cascade')->onUpdate('cascade');

        });
    }

    public function down()
    {
        Schema::dropIfExists('invoice_details');
    }
}
