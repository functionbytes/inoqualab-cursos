<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mail_auto_confirm_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enterprise_id')->constrained('enterprises')->cascadeOnDelete();
            $table->unsignedTinyInteger('min_confidence')->default(90);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('enterprise_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_auto_confirm_rules');
    }
};
