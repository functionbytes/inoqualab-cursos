<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDistributorStaffTable extends Migration
{
    public function up(): void
    {
        Schema::create('distributor_staff', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('distributor_id')->nullable();
            $table->tinyInteger('available')->nullable()->default(0);
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('distributor_id')->references('id')->on('distributors')->onDelete('cascade')->onUpdate('cascade');
            $table->index(['user_id', 'distributor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distributor_staff');
    }
}
