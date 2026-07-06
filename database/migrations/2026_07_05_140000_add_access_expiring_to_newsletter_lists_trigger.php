<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Amplía el enum `trigger` de newsletter_lists para admitir la nueva lista
 * dinámica de acceso por vencer. Se usa SQL directo porque `->change()` sobre
 * enums con doctrine/dbal es frágil.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE newsletter_lists MODIFY COLUMN `trigger` ENUM('manual', 'course_completed', 'certificate_expiring', 'course_access_expiring') NOT NULL DEFAULT 'manual'");
    }

    public function down(): void
    {
        // Revierte a listas manuales las filas del nuevo trigger antes de quitarlo del enum.
        DB::table('newsletter_lists')->where('trigger', 'course_access_expiring')->update(['trigger' => 'manual']);
        DB::statement("ALTER TABLE newsletter_lists MODIFY COLUMN `trigger` ENUM('manual', 'course_completed', 'certificate_expiring') NOT NULL DEFAULT 'manual'");
    }
};
