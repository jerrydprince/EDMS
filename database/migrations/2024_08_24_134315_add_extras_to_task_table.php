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
        Schema::table('task', function (Blueprint $table) {
            $table->unsignedBigInteger('assigned_by_division')->nullable();
            $table->unsignedBigInteger('assigned_by_department')->nullable();
            $table->foreign('assigned_by_division')->references('id')->on('divisions');
            $table->foreign('assigned_by_department')->references('id')->on('departments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task', function (Blueprint $table) {
            //
        });
    }
};
