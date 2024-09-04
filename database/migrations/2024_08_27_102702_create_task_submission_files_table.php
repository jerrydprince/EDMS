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
        Schema::create('task_submission_files', function (Blueprint $table) {
            $table->id();
            $table->string('path');
            $table->unsignedBigInteger('task_submissions_id');
            $table->unsignedBigInteger('task_id');
            $table->foreign('task_submissions_id')->references('id')->on('task_submissions');
            $table->foreign('task_id')->references('id')->on('task');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_submission_files');
    }
};
