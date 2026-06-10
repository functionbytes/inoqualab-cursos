<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketassignchildrenTable extends Migration
{
    public function up()
    {
        Schema::create('ticketassignchildren', function (Blueprint $table) {
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('toassignuser_id');
            $table->primary(['toassignuser_id', 'ticket_id']);
            $table->foreign('toassignuser_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('ticket_id')->references('id')->on('tickets')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ticketassignchildren');
    }
}
