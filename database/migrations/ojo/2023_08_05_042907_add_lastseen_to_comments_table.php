<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLastseenToCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->timestamp('lastseen')->nullable()->after('display');
            $table->string('emailcommentfile')->nullable()->after('comment');
        });
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('tickettype')->nullable()->after('emailticketfile');
            $table->string('MessageID')->nullable()->after('tickettype');
            $table->string('ticketviolation')->nullable()->after('MessageID');
            $table->string('ticketviolationnote')->nullable()->after('ticketviolation');
            $table->timestamp('employeereplytime')->nullable()->after('employeesreplying');
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->string('violatednote')->nullable()->after('voilated');
            $table->string('google2fa_secret')->nullable()->after('userType');
        });
        Schema::table('customer_settings', function (Blueprint $table) {
            $table->string('twofactorauth')->nullable()->after('darkmode');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->string('google2fa_secret')->nullable()->after('status');
            $table->string('twofactorauth')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('comments', function (Blueprint $table) {
            //
        });
    }
}
