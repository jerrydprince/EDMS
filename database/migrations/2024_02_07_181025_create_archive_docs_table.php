<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('archive_docs', function (Blueprint $table) {
            $table->id();
            $table->string('description')->nullable();
            $table->text('text')->nullable();
            $table->date('date')->nullable();
            $table->bigInteger('number')->nullable();
            $table->text('list')->nullable();
            $table->unsignedBigInteger('archive_id');
            $table->foreign('archive_id')->references('id')->on('archives');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archive_docs');
    }
};
