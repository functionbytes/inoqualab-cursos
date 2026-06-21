<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('newsletters', function (Blueprint $table) {
            if (! Schema::hasColumn('newsletters', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('source');
            }
            if (! Schema::hasColumn('newsletters', 'subscribed_at')) {
                $table->timestamp('subscribed_at')->nullable()->after('is_active');
            }
            if (! Schema::hasColumn('newsletters', 'unsubscribed_at')) {
                $table->timestamp('unsubscribed_at')->nullable()->after('subscribed_at');
            }

            $table->index(['is_active', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('newsletters', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'created_at']);
            $table->dropColumn(['ip_address', 'subscribed_at', 'unsubscribed_at']);
        });
    }
};
