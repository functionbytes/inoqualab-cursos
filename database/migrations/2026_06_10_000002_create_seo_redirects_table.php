<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_redirects', function (Blueprint $table) {
            $table->id();
            $table->string('source_path');
            $table->string('target_path');
            $table->unsignedSmallInteger('status_code')->default(301);
            $table->boolean('is_regex')->default(false);
            $table->boolean('is_wildcard')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('hits_count')->default(0);
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index('source_path');
            $table->index(['source_path', 'is_active']);
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_redirects');
    }
};
