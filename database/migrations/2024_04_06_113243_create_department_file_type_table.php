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
        Schema::create('department_file_type', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('file_type_id');
            $table->unsignedBigInteger('department_id');
            $table->foreign('file_type_id')->references('id')->on('file_types');
            $table->foreign('department_id')->references('id')->on('departments');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_file_type');
    }
};
