<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEnterpriseStaffTable extends Migration
{
    public function up(): void
    {
        Schema::create('enterprise_staff', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('enterprise_id')->nullable();
            $table->tinyInteger('available')->nullable()->default(0);
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('enterprise_id')->references('id')->on('enterprises')->onDelete('cascade')->onUpdate('cascade');
            $table->index(['user_id', 'enterprise_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enterprise_staff');
    }
}
