<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_metas', function (Blueprint $table) {
            $table->id();
            $table->string('seoable_type');
            $table->unsignedBigInteger('seoable_id');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('keywords')->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image')->nullable();
            $table->string('og_type')->default('website');
            $table->string('twitter_card')->default('summary_large_image');
            $table->string('twitter_title')->nullable();
            $table->text('twitter_description')->nullable();
            $table->string('twitter_image')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('robots')->default('index,follow');
            $table->string('schema_type')->nullable();
            $table->json('schema_custom')->nullable();
            $table->unsignedTinyInteger('seo_score')->nullable();
            $table->string('seo_grade', 1)->nullable();
            $table->timestamp('seo_audited_at')->nullable();
            $table->string('target_keyword')->nullable();
            $table->timestamps();

            $table->unique(['seoable_type', 'seoable_id']);
            $table->index('robots');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_metas');
    }
};
