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
        Schema::create('file_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')->constrained('files')->onDelete('cascade');
            $table->foreignId('division_id')->nullable()->constrained('divisions')->onDelete('set null');
            $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('set null');
            $table->foreignId('sent_from_division_id')->nullable()->constrained('divisions')->onDelete('set null');
            $table->foreignId('sent_from_unit_id')->nullable()->constrained('units')->onDelete('set null');
            $table->foreignId('sent_from_department_id')->nullable()->constrained('departments')->onDelete('set null');
            $table->foreignId('sent_to_department_id')->nullable()->constrained('departments')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('file_movements', function (Blueprint $table) {
            $table->foreignId(['file_id']);
            $table->foreignId(['division_id']);
            $table->foreignId(['unit_id']);
            $table->foreignId(['sent_from_division_id']);
            $table->foreignId(['sent_from_unit_id']);
            $table->foreignId(['sent_from_department_id']);
            $table->foreignId(['sent_to_department_id']);
        });
    }
};
