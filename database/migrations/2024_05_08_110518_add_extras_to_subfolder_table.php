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
        Schema::table('sub_folders', function (Blueprint $table) {
            $table->string('name');
            $table->string('number');
            $table->string('year');
            $table->unsignedBigInteger('file_type_id');
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('division_id');
            $table->unsignedBigInteger('unit_id');
            $table->text('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subfolder', function (Blueprint $table) {
            //
        });
    }
};
