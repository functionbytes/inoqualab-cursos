<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDistributorEnterpriseTable extends Migration
{
    public function up(): void
    {
        Schema::create('distributor_enterprise', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('enterprise_id')->nullable();
            $table->unsignedBigInteger('distributor_id')->nullable();
            $table->tinyInteger('available')->default(1);
            $table->timestamps();
            $table->foreign('enterprise_id')->references('id')->on('enterprises')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('distributor_id')->references('id')->on('distributors')->onDelete('cascade')->onUpdate('cascade');
            $table->index(['enterprise_id', 'distributor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distributor_enterprise');
    }
}
