<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enterprise_aliases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enterprise_id')->constrained('enterprises')->cascadeOnDelete();
            $table->string('alias_type', 20);
            $table->string('alias_value', 255);
            $table->string('normalized_value', 255)->index();
            $table->timestamps();

            $table->unique(['alias_type', 'normalized_value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enterprise_aliases');
    }
};
