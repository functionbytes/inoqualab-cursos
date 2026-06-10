<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_404_logs', function (Blueprint $table) {
            $table->id();
            $table->string('path');
            $table->string('referer')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('ip', 45)->nullable();
            $table->unsignedBigInteger('hit_count')->default(1);
            $table->boolean('has_redirect')->default(false);
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->unique('path');
            $table->index('hit_count');
            $table->index('last_seen_at');
            $table->index('has_redirect');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_404_logs');
    }
};
