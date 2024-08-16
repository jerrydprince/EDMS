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
            $table->integer('sent_from');
            $table->integer('sent_to');
            $table->unsignedBigInteger('file_id');
            $table->boolean('is_forwarded')->default(0);
            $table->enum('status', ['pending', 'rejected', 'delivered']);
            $table->bigInteger('file_movement_id');
            $table->integer('unit_id')->nullable();
            $table->integer('division_id')->nullable();
            $table->integer('department_id')->nullable();
            $table->foreign('file_id')->references('id')->on('files');
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
