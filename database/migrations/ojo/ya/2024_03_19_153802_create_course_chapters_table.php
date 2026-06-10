<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoursesChaptersTable extends Migration
{
    public function up(): void
    {
        Schema::create('course_chapters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->integer('position')->nullable();
            $table->tinyInteger('available')->nullable()->default(0);
            $table->unsignedBigInteger('course_id')->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course')->onDelete('cascade')->onUpdate('cascade');
            $table->index(['course_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_chapters');
    }
}
