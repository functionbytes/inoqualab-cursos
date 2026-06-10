<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCertifiersTable extends Migration
{
    public function up(): void
    {
        Schema::create('certifiers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('slack', 30)->unique();
            $table->string('firstname');
            $table->string('lastname');
            $table->string('identification');
            $table->string('profession');
            $table->text('description')->nullable();
            $table->tinyInteger('available')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certifiers');
    }
}
