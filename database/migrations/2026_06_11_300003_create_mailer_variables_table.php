<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mailer_variables', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            $table->string('key');
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('example_value')->nullable();
            $table->string('category')->default('general');
            $table->string('module')->default('core');
            $table->boolean('is_system')->default(false);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->unique(['key', 'module']);
            $table->index('is_enabled');
            $table->index('module');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mailer_variables');
    }
};
