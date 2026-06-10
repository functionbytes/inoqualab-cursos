<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCertificatesTable extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->unsignedBigInteger('inscription_id')->nullable();
            $table->unsignedBigInteger('exam_id')->nullable();
            $table->unsignedBigInteger('certifier_id')->nullable();
            $table->unsignedBigInteger('certification_id')->nullable();
            $table->date('start_at')->nullable();
            $table->date('end_at')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('inscription_id')->references('id')->on('inscriptions')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('exam_id')->references('id')->on('exams')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('certifier_id')->references('id')->on('certifiers')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('certification_id')->references('id')->on('certificationS')->onDelete('cascade')->onUpdate('cascade');
            $table->index(['user_id', 'course_id', 'inscription_id', 'exam_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
}
