<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Registro de corridas de los comandos de remarketing (found/sent por cohorte)
 * para alimentar la página de estado de automatizaciones.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('remarketing_runs', function (Blueprint $table) {
            $table->id();
            $table->string('command', 100);
            $table->date('cohort_date')->nullable();
            $table->unsignedInteger('found')->default(0);
            $table->unsignedInteger('sent')->default(0);
            $table->timestamps();
            $table->index(['command', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('remarketing_runs');
    }
};
