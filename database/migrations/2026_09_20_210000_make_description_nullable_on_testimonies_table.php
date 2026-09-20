<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// El StoreTestimonieRequest/UpdateTestimonieRequest ya declaraban `description`
// como `nullable` (el testimonio se resume en firstname/lastname/role/rating),
// pero la columna real en BD seguia siendo NOT NULL (creada fuera del flujo de
// migraciones normal, ver database/migrations/ojo/ya/2024_03_19_..._create_testimonies_table.php
// que SI la declaraba nullable pero nunca corrio contra esta BD). Al alinear
// la validacion del cliente (JS) con el FormRequest en esta misma sesion de QA,
// guardar un testimonio sin cuerpo paso a dar 500 real:
// "SQLSTATE[23000]: Integrity constraint violation: 1048 Column 'description' cannot be null".
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('testimonies', function (Blueprint $table) {
            $table->text('description')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('testimonies', function (Blueprint $table) {
            $table->text('description')->nullable(false)->change();
        });
    }
};
