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
        Schema::create('division_file_type', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('file_type_id');
            $table->unsignedBigInteger('division_id');
            $table->foreign('file_type_id')->references('id')->on('file_types');
            $table->foreign('division_id')->references('id')->on('divisions');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('division_file_type');
    }
};
