<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Elimina las tablas del módulo Account (feature abandonada): sus modelos ya se
 * borraron, no se usan en ningún flujo y las tablas están vacías (0 filas).
 * down() las recrea con su esquema original para mantener la migración reversible.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Orden FK-safe: primero las que dependen de otras.
        Schema::dropIfExists('account_items');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('account_type');
    }

    public function down(): void
    {
        Schema::create('account_type', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('slack', 30)->unique();
            $table->string('title', 250);
            $table->text('description')->nullable();
            $table->tinyInteger('available')->default(1);
            $table->timestamps();
            $table->index(['available']);
        });

        Schema::create('accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('slack', 30)->unique();
            $table->unsignedBigInteger('distributor_id');
            $table->unsignedBigInteger('type_id');
            $table->text('description')->nullable();
            $table->tinyInteger('available')->default(1);
            $table->index(['distributor_id', 'type_id', 'available']);
            $table->timestamps();
            $table->foreign('distributor_id')->references('id')->on('distributors')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('type_id')->references('id')->on('account_type')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::create('account_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('slack', 30)->unique();
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('account_id');
            $table->decimal('quantity', 8, 2)->default(0);
            $table->decimal('usage', 8, 2)->default(0);
            $table->timestamps();
            $table->index(['course_id', 'account_id'], 'account_items_indexes');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade')->onUpdate('cascade');
        });
    }
};
