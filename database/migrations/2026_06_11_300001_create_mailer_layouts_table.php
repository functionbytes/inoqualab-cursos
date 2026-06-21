<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mailer_layouts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            $table->string('name');
            $table->string('alias')->unique();
            $table->string('code')->nullable();
            $table->string('type')->default('layout');
            $table->string('group_name')->nullable();
            $table->string('subject')->nullable();
            $table->longText('content')->nullable();
            $table->boolean('is_protected')->default(false);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->index('alias');
            $table->index('is_enabled');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mailer_layouts');
    }
};
