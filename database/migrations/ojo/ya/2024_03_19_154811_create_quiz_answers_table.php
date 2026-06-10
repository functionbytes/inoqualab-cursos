<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuizsAnswersTable extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_answers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('quiz_id')->nullable();
            $table->unsignedBigInteger('lesson_id')->nullable();
            $table->unsignedBigInteger('topic_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('question_id')->nullable();
            $table->char('user_answer')->nullable();
            $table->char('answer')->nullable();
            $table->string('type')->nullable();
            $table->tinyInteger('approved')->nullable()->default(0);
            $table->timestamps();
            $table->foreign('quiz_id')->references('id')->on('quizs')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('lesson_id')->references('id')->on('course_lessons')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('topic_id')->references('id')->on('quiz_topics')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('question_id')->references('id')->on('quiz_questions')->onDelete('cascade')->onUpdate('cascade');
            $table->index(['quiz_id', 'lesson_id', 'topic_id', 'user_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_answers');
    }
}
