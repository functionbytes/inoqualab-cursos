<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Campos para mostrar los testimonios en un carrusel del home (antes solo
// nombre/apellido/descripción, sin rol, icono de sector, calificación ni el
// resultado que el cliente destaca -- ver home-testimonials del sitio hermano
// inoqualab.test, que este carrusel adapta a cursos/capacitación).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('testimonies', function (Blueprint $table) {
            $table->string('role')->nullable()->after('lastname');
            $table->string('icon')->nullable()->after('role');
            $table->unsignedTinyInteger('rating')->default(5)->after('icon');
            $table->string('benefit')->nullable()->after('rating');
            $table->unsignedInteger('position')->default(0)->after('benefit');
            $table->index('position');
        });
    }

    public function down(): void
    {
        Schema::table('testimonies', function (Blueprint $table) {
            $table->dropIndex(['position']);
            $table->dropColumn(['role', 'icon', 'rating', 'benefit', 'position']);
        });
    }
};
