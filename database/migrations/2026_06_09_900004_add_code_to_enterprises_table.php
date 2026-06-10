<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('enterprises', 'code')) {
            return;
        }

        Schema::table('enterprises', function (Blueprint $table) {
            $table->string('code', 50)->nullable()->unique()->after('nit');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('enterprises', 'code')) {
            return;
        }

        Schema::table('enterprises', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropColumn('code');
        });
    }
};
