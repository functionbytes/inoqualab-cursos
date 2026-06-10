<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Las órdenes generadas automáticamente desde correos entrantes no tienen un
 * staff humano que las oficialice, por lo que staff_id debe poder ser nulo.
 * En el flujo de la bandeja de revisión sí se guarda el id del operador.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders_activity', function (Blueprint $table) {
            $table->unsignedBigInteger('staff_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders_activity', function (Blueprint $table) {
            $table->unsignedBigInteger('staff_id')->nullable(false)->change();
        });
    }
};
