<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50)->index();
            $table->enum('severity', ['info', 'warning', 'critical'])->default('warning');
            $table->string('title', 200);
            $table->text('message')->nullable();
            $table->json('context')->nullable();
            $table->string('url', 500)->nullable();
            $table->timestamp('acknowledged_at')->nullable()->index();
            $table->unsignedBigInteger('acknowledged_by')->nullable();
            $table->timestamps();

            $table->index(['severity', 'acknowledged_at']);
            $table->index(['type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_alerts');
    }
};
