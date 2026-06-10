<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExamQuestionsTable extends Migration
{
    public function up(): void
    {
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('slack', 30)->unique();
            $table->string('question')->nullable();
            $table->string('a')->nullable();
            $table->string('b')->nullable();
            $table->string('c')->nullable();
            $table->string('d')->nullable();
            $table->string('type')->nullable();
            $table->string('answer')->nullable();
            $table->tinyInteger('available')->nullable()->default(0);
            $table->unsignedBigInteger('course_id')->nullable();
            $table->unsignedBigInteger('topic_id')->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('topic_id')->references('id')->on('exam_topics')->onDelete('cascade')->onUpdate('cascade');
            $table->index(['course_id', 'topic_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_questions');
    }
}
