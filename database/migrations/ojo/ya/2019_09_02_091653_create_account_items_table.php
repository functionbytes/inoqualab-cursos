<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountItemsTable extends Migration
{
    public function up()
    {
        Schema::create('account_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('slack', 30)->unique();
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('account_id');
            $table->decimal('quantity', 8, 2)->default(0);
            $table->decimal('usage', 8, 2)->default(0);
            $table->timestamps();
            $table->index(['course_id', 'account_id'], 'account_items_indexes');

            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade')->onUpdate('cascade');

        });
    }

    public function down()
    {
        Schema::dropIfExists('account_items');
    }
}
