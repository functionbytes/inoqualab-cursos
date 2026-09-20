<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// StoreSliderRequest/UpdateSliderRequest ya declaraban `subtitle` y `description`
// como `nullable` (solo `title` es obligatorio para un banner), pero ambas
// columnas seguian siendo NOT NULL en BD. Al alinear la validacion del cliente
// (JS) con el FormRequest en esta misma sesion de QA, crear un banner sin
// subtitulo/descripcion paso a dar 500 real:
// "SQLSTATE[23000]: Integrity constraint violation: 1048 Column 'subtitle' cannot be null".
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sliders', function (Blueprint $table) {
            $table->string('subtitle')->nullable()->change();
            $table->text('description')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('sliders', function (Blueprint $table) {
            $table->string('subtitle')->nullable(false)->change();
            $table->text('description')->nullable(false)->change();
        });
    }
};
