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
        Schema::create('folder', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('number');
            $table->string('department_id');
            $table->string('division_id')->nullable();
            $table->string('unit_id')->nullable();
            $table->boolean('is_open')->default(1);
            $table->integer('file_count')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('folder');
    }
};
