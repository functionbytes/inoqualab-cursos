<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImapSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('imap_settings', function (Blueprint $table) {
            $table->id();
            $table->string('imap_host');
            $table->string('imap_port');
            $table->string('imap_protocol');
            $table->string('imap_encryption');
            $table->string('imap_username');
            $table->string('imap_password');
            $table->bigInteger('category_id')->nullable()->unsigned();
            $table->integer('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('imap_settings');
    }
}
