<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceItemsTable extends Migration
{
    public function up()
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('slack', 30)->unique();
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('invoice_id');
            $table->decimal('quantity', 8, 2)->default(0);
            $table->decimal('amount', 13, 2)->default(0);
            $table->timestamps();
            $table->index(['invoice_id', 'course_id'], 'invoice_items_indexes');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade')->onUpdate('cascade');

        });
    }

    public function down()
    {
        Schema::dropIfExists('invoice_items');
    }
}
