<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tickethistories', function (Blueprint $table) {
            $table->boolean('ticketnote')->nullable()->after('ticketactions');
            $table->string('overduestatus')->nullable()->after('ticketactions');
            $table->string('replystatus')->nullable()->after('ticketactions');
            $table->string('ticketviolation')->nullable()->after('ticketactions');
            $table->string('oldcomment')->nullable()->after('ticketactions');
            $table->string('commentmodify')->nullable()->after('ticketactions');
            $table->string('currentAction')->nullable()->after('ticketactions');
            $table->longText('assignUser')->nullable()->after('ticketactions');
            $table->string('status')->nullable()->after('ticketactions');
            $table->string('username')->nullable()->after('ticketactions');
            $table->string('type')->nullable()->after('ticketactions');
        });

        Schema::table('senduserlists', function (Blueprint $table) {
            // Drop existing foreign keys
            $table->dropForeign(['mail_id']);
            $table->dropForeign(['touser_id']);
            $table->dropForeign(['tocust_id']);

            // Add new foreign keys
            $table->foreign('mail_id')->references('id')->on('sendmails')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('touser_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('tocust_id')->references('id')->on('customers')->onUpdate('cascade')->onDelete('cascade');
        });

        Schema::table('email_templates', function (Blueprint $table) {
            $table->longtext('variables_used')->nullable()->after('body');
        });

        Schema::table('footertexts', function (Blueprint $table) {
            $table->longText('copyright')->nullable()->change()->after('id');
        });

        Schema::table('social_auth_settings', function (Blueprint $table) {

            $table->dropColumn('facebook_client_id');
            $table->dropColumn('facebook_secret_id');
            $table->dropColumn('facebook_status');

            $table->dropColumn('twitter_client_id');
            $table->dropColumn('twitter_secret_id');
            $table->dropColumn('twitter_status');

            $table->string('microsoft_app_id')->nullable();
            $table->string('microsoft_secret_id')->nullable();
            $table->enum('microsoft_status', ['enable', 'disable'])->default('disable');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->string('importantticket')->nullable()->after('storage_disk');
        });

        Schema::table('ticket_customfields', function (Blueprint $table) {
            $table->string('fieldoptions')->nullable()->after('fieldtypes');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->integer('phoneVerified')->after('phone')->default(0);
            $table->integer('phonesmsenable')->after('phoneVerified')->default(0);
        });

        Schema::table('cannedmessages', function (Blueprint $table) {
            $table->string('responsetype')->nullable()->after('messages');
        });

        Schema::table('announcements', function (Blueprint $table) {
            $table->string('buttonlable')->nullable()->after('status');
            $table->string('buttonurl')->nullable()->after('buttonlable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickethistories', function (Blueprint $table) {
            //
        });

        Schema::table('social_auth_settings', function (Blueprint $table) {
            $table->string('facebook_client_id')->nullable();
            $table->string('facebook_secret_id')->nullable();
            $table->enum('facebook_status', ['enable', 'disable'])->default('disable');
            $table->string('twitter_client_id')->nullable();
            $table->string('twitter_secret_id')->nullable();
            $table->enum('twitter_status', ['enable', 'disable'])->default('disable');
        });
    }
};
