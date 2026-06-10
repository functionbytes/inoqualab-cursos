<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuizsTopicsTable extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_topics', function (Blueprint $table) {
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
            $table->unsignedBigInteger('lesson_id')->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->timestamps();
            $table->foreign('lesson_id')->references('id')->on('lessons')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('course_id')->references('id')->on('course')->onDelete('cascade')->onUpdate('cascade');
            $table->index(['lesson_id', 'course_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_topics');
    }
}
