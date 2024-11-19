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
            // $table->text('description')->nullable();
            // $table->string('text')->nullable();
            // $table->date('date')->nullable();
            // $table->string('number')->nullable();
            // $table->text('list')->nullable();
            // $table->unsignedBigInteger('archive_id');
            // $table->foreign('archive_id')->references('id')->on('archives');
            $table->string('archive_keys');
            $table->text('archive_values');
            $table->unsignedBigInteger('archive_id');
            $table->foreign('archive_id')->references('id')->on('archive');
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
