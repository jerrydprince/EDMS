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
        Schema::create('task_assignment_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_assignment_id');
            $table->foreign('task_assignment_id')->references('id')->on('task_assignment_id')->onDelete('cascade');
            $table->unsignedBigInteger('sent_by');
            $table->unsignedBigInteger('sent_to');
            $table->text('comments');
            $table->unsignedBigInteger('comment_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_assignment_comments');
    }
};
