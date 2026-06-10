<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEnterprisesTable extends Migration
{
    public function up(): void
    {
        Schema::create('enterprises', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('slack', 30)->unique();
            $table->string('title', 250);
            $table->string('slug', 250);
            $table->string('address', 250);
            $table->string('cellphone', 250);
            $table->string('nit', 250);
            $table->string('email', 250);
            $table->string('leading', 250);
            $table->string('supporting', 250);
            $table->tinyInteger('available')->default(1);
            $table->timestamps();
            $table->index(['available']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enterprises');
    }
}
