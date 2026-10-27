<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_slas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workflow_step_id')->nullable();
            $table->integer('duration_hours');
            $table->string('escalation_role')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_slas');
    }
};