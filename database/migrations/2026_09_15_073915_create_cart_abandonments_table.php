<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cart_abandonments', function (Blueprint $table) {
            $table->id();
            $table->string('slack', 20)->unique();
            $table->string('email', 191);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('items');
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamp('reminded_at')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();

            $table->index('email');
            $table->index(['reminded_at', 'converted_at', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_abandonments');
    }
};
