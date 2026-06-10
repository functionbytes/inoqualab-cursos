<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('slack', 30)->unique();
            $table->string('number', 50)->unique();
            $table->string('reference', 30)->nullable();
            $table->date('date')->nullable();
            $table->date('due_date')->nullable();
            $table->decimal('total_discount_amount', 13, 2)->default(0);
            $table->decimal('total_after_discount', 13, 2)->default(0);
            $table->decimal('total_tax_amount', 13, 2)->default(0);
            $table->decimal('total_invoices_amount', 13, 2)->default(0);
            $table->text('notes')->nullable();
            $table->tinyInteger('available')->default(1);
            $table->unsignedBigInteger('distributor_id');
            $table->unsignedBigInteger('method_id');
            $table->unsignedBigInteger('condition_id');
            $table->timestamps();
            $table->foreign('distributor_id')->references('id')->on('distributors')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('method_id')->references('id')->on('invoice_method')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('condition_id')->references('id')->on('invoice_condition')->onDelete('cascade')->onUpdate('cascade');

            $table->index(['distributor_id', 'method_id', 'condition_id', 'number'], 'invoice_indexes');
        });

    }

    public function down()
    {
        Schema::dropIfExists('invoices');
    }
}
