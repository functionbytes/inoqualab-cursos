<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Rediseño de la sección del home calcado del template Finsta: reemplaza el
// texto de "resultado destacado" (columna benefit, que se conserva como
// descripción bajo el contador) por un contador numérico grande, ej.
// "50K+" -> counter_value=50, counter_suffix=K+.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('testimonies', function (Blueprint $table) {
            $table->string('counter_value', 20)->nullable()->after('position');
            $table->string('counter_suffix', 10)->nullable()->after('counter_value');
        });
    }

    public function down(): void
    {
        Schema::table('testimonies', function (Blueprint $table) {
            $table->dropColumn(['counter_value', 'counter_suffix']);
        });
    }
};
