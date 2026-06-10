<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoursesClassesTable extends Migration
{
    public function up(): void
    {
        Schema::create('course_classes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('slack', 30)->unique();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->integer('timer')->nullable();
            $table->integer('per_q_mark')->nullable();
            $table->integer('show_ans')->nullable();
            $table->string('type')->nullable();
            $table->integer('due_days')->nullable();
            $table->string('type')->nullable();
            $table->tinyInteger('available')->nullable()->default(0);
            $table->tinyInteger('quiz_again')->nullable()->default(0);
            $table->unsignedBigInteger('course_id')->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course')->onDelete('cascade')->onUpdate('cascade');
            $table->index(['course_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_classes');
    }
}
