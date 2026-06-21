<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_events', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->index();
            $table->string('status');
            $table->string('reference')->nullable()->index();
            $table->json('payload')->nullable();
            $table->timestamps();

            // Cada (transacción, estado) se procesa una sola vez: idempotencia de webhooks.
            $table->unique(['transaction_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_events');
    }
};
