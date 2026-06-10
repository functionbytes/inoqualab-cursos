<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_aliases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('alias', 255);
            $table->string('normalized_alias', 255)->unique()->index();
            $table->string('source', 30)->default('mail');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_aliases');
    }
};
