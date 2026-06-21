<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seo_metas', function (Blueprint $table) {
            $table->string('locale', 10)->nullable()->after('seoable_id');
            $table->unsignedInteger('gsc_clicks')->nullable()->after('target_keyword');
            $table->unsignedInteger('gsc_impressions')->nullable()->after('gsc_clicks');
            $table->decimal('gsc_position', 5, 1)->nullable()->after('gsc_impressions');
            $table->timestamp('gsc_updated_at')->nullable()->after('gsc_position');

            // Drop old unique and replace with locale-aware unique
            $table->dropUnique(['seoable_type', 'seoable_id']);
            $table->unique(['seoable_type', 'seoable_id', 'locale'], 'seo_metas_seoable_locale_unique');
        });
    }

    public function down(): void
    {
        Schema::table('seo_metas', function (Blueprint $table) {
            $table->dropUnique('seo_metas_seoable_locale_unique');
            $table->unique(['seoable_type', 'seoable_id']);
            $table->dropColumn(['locale', 'gsc_clicks', 'gsc_impressions', 'gsc_position', 'gsc_updated_at']);
        });
    }
};
