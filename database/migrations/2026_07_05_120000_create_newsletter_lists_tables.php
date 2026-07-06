<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_lists', function (Blueprint $table) {
            $table->id();
            $table->string('slack', 191)->unique();
            $table->string('name', 255);
            $table->string('description', 500)->nullable();
            // manual = gestionada a mano; el resto son listas dinámicas que se
            // pueblan/vacían solas por eventos de ciclo de vida.
            $table->enum('trigger', ['manual', 'course_completed', 'certificate_expiring'])->default('manual');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index('trigger');
        });

        Schema::create('newsletter_list_subscriber', function (Blueprint $table) {
            $table->id();
            $table->foreignId('newsletter_list_id')->constrained('newsletter_lists')->cascadeOnDelete();
            // newsletters.id es bigint SIGNED (tabla antigua), no unsigned: el tipo
            // debe coincidir exacto o el FK queda mal formado (errno 150).
            $table->bigInteger('newsletter_id');
            $table->string('added_reason', 191)->nullable();
            $table->timestamps();
            // Nombre explícito y corto: el auto-generado excede el límite de 64 chars de MySQL.
            $table->unique(['newsletter_list_id', 'newsletter_id'], 'nls_subscriber_unique');
            $table->foreign('newsletter_id')->references('id')->on('newsletters')->cascadeOnDelete();
        });

        Schema::table('newsletter_campaigns', function (Blueprint $table) {
            // null = enviar a todos los suscriptores (comportamiento actual).
            $table->foreignId('newsletter_list_id')->nullable()->after('uid')
                ->constrained('newsletter_lists')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('newsletter_campaigns', function (Blueprint $table) {
            $table->dropForeign(['newsletter_list_id']);
            $table->dropColumn('newsletter_list_id');
        });
        Schema::dropIfExists('newsletter_list_subscriber');
        Schema::dropIfExists('newsletter_lists');
    }
};
