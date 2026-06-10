<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incoming_mails', function (Blueprint $table) {
            $table->id();
            $table->string('slack', 6)->unique();
            $table->string('message_id', 255)->unique();
            $table->string('from', 255)->index();
            $table->string('subject', 500)->nullable();
            $table->timestamp('received_at')->nullable();
            $table->longText('raw_body')->nullable();
            $table->json('parsed_payload')->nullable();
            $table->string('status', 30)->default('pending_review')->index();
            $table->unsignedTinyInteger('confidence_score')->nullable();
            $table->foreignId('matched_enterprise_id')->nullable()->constrained('enterprises')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->text('error_log')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incoming_mails');
    }
};
