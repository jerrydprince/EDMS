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
        Schema::create('archive_file_path', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('archive_id');
            $table->unsignedBigInteger('file_path_id');
            $table->foreign('archive_id')->references('id')->on('archives');
            $table->foreign('file_path_id')->references('id')->on('file_paths');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archive_file_path');
    }
};
