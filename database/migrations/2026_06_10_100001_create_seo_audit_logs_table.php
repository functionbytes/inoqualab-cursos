<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_audit_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('seo_meta_id')->nullable();
            $table->string('url')->nullable();
            $table->tinyInteger('score')->unsigned();
            $table->char('grade', 1);
            $table->unsignedSmallInteger('issues_count')->default(0);
            $table->json('issues')->nullable();
            $table->unsignedSmallInteger('passed_count')->default(0);
            $table->timestamp('audited_at');
            $table->timestamps();

            $table->foreign('seo_meta_id')->references('id')->on('seo_metas')->onDelete('cascade');
            $table->index('seo_meta_id');
            $table->index('audited_at');
            $table->index('grade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_audit_logs');
    }
};
