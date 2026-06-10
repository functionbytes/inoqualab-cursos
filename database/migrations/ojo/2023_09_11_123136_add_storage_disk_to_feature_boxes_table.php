<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStorageDiskToFeatureBoxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('storage_disk')->nullable()->after('image');
            $table->timestamp('last_activity')->nullable()->after('status');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->string('storage_disk')->nullable()->after('image');
            $table->timestamp('last_activity')->nullable()->after('status');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->string('storage_disk')->nullable()->after('tickettype');
            $table->integer('imap_id')->nullable()->after('tickettype');
            $table->string('ticketreopen')->nullable()->after('imap_id');
            $table->string('item_name')->nullable()->after('purchasecode');

        });

        Schema::table('comments', function (Blueprint $table) {
            $table->string('storage_disk')->nullable()->after('emailcommentfile');
        });

        Schema::table('feature_boxes', function (Blueprint $table) {
            $table->string('storage_disk')->nullable()->after('image');
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->string('storage_disk')->nullable()->after('featureimage');
        });

        Schema::table('testimonials', function (Blueprint $table) {
            $table->string('storage_disk')->nullable()->after('image');
        });

        Schema::table('callactions', function (Blueprint $table) {
            $table->string('storage_disk')->nullable()->after('image');
        });

        Schema::table('envatoapitoken', function (Blueprint $table) {
            $table->string('envatoapitokensecond')->nullable()->after('envatoapitoken');
            $table->string('envatoapitokenthird')->nullable()->after('envatoapitokensecond');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('feature_boxes', function (Blueprint $table) {
            //
        });
    }
}
