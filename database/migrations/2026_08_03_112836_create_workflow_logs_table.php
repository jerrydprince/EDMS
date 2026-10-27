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
        Schema::create('workflow_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('action_by');
            $table->unsignedBigInteger('action_to')->nullable();
            $table->string('action_type'); // delegated, submitted, approved, returned
            $table->text('comments')->nullable();
            $table->timestamps();

            $table->foreign('task_id')->references('id')->on('task')->onDelete('cascade');
            $table->foreign('action_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('action_to')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_logs');
    }
};
