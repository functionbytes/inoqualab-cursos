<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Marca cuándo se envió el recordatorio de pago de una orden abandonada,
            // para no enviarlo más de una vez.
            $table->timestamp('reminded_at')->nullable()->after('payment_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('reminded_at');
        });
    }
};
