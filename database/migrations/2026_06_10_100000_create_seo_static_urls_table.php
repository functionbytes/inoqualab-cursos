<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_static_urls', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('url', 500);
            $table->decimal('priority', 3, 1)->default(0.5);
            $table->string('changefreq', 20)->default('weekly');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique('url');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_static_urls');
    }
};
