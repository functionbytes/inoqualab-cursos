<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentsTable extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('slack', 30)->unique();
            $table->string('title', 250);
            $table->text('description');
            $table->tinyInteger('available')->default(1);
            $table->timestamps();
            $table->index(['available']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
}
