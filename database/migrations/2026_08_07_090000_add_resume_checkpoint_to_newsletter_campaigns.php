<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SendNewsletterCampaignJob no trackeaba qué suscriptor ya había recibido el
 * correo: si el job fallaba a mitad de camino (timeout a los 3600s con
 * $tries=1) y el admin la reintentaba (retry() → resetea a 'draft' → send()
 * la relanza desde cero), el job volvía a recorrer TODA la lista de
 * suscriptores -- los ya enviados en el intento anterior recibían el
 * correo dos veces.
 *
 * last_sent_newsletter_id es el checkpoint: el job ordena por
 * newsletters.id y lo persiste después de CADA lote (no solo al final), así
 * que un reintento retoma justo donde se quedó en vez de repetir desde el
 * principio.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('newsletter_campaigns', function (Blueprint $table) {
            $table->unsignedBigInteger('last_sent_newsletter_id')->nullable()->after('failed_count');
        });
    }

    public function down(): void
    {
        Schema::table('newsletter_campaigns', function (Blueprint $table) {
            $table->dropColumn('last_sent_newsletter_id');
        });
    }
};
