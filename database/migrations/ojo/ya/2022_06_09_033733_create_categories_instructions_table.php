<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriesInstructionsTable extends Migration
{
    public function up()
    {
        Schema::create('categories_instructions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('slack', 30)->unique();
            $table->string('title');
            $table->longText('slug')->nullable();
            $table->tinyInteger('available')->default(1);
            $table->timestamps();
        });

    }

    public function down()
    {
        Schema::dropIfExists('categories_instructions');
    }
}
