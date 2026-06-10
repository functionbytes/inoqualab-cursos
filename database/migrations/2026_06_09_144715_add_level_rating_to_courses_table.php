<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (! Schema::hasColumn('courses', 'level')) {
                // Principiante | Intermedio | Avanzado
                $table->string('level')->nullable()->after('featured');
            }
            if (! Schema::hasColumn('courses', 'rating')) {
                // Calificación 0.0 – 5.0
                $table->decimal('rating', 2, 1)->default(0)->after('level');
            }
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (Schema::hasColumn('courses', 'level')) {
                $table->dropColumn('level');
            }
            if (Schema::hasColumn('courses', 'rating')) {
                $table->dropColumn('rating');
            }
        });
    }
};
