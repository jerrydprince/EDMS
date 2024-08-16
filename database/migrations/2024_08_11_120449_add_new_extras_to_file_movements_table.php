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
        Schema::table('file_movements', function (Blueprint $table) {
            $table->integer('sent_from_unit')->nullable();
            $table->integer('sent_from_division')->nullable();
            $table->integer('sent_from_department')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('file_movements', function (Blueprint $table) {
            //
        });
    }
};
