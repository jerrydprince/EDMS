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
        Schema::create('workflow_routes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->string('current_level'); // head, department, division, unit, staff
            $table->string('direction'); // downward_delegation, upward_approval
            $table->string('status')->default('pending'); // pending, in_progress, returned, approved
            $table->timestamps();

            $table->foreign('task_id')->references('id')->on('task')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_routes');
    }
};
