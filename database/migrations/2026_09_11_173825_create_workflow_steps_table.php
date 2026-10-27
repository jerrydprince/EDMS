<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workflow_version_id')->nullable();
            $table->string('name');
            $table->string('step_type')->default('approval')->comment('approval, review, input');
            $table->string('role_required')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->boolean('is_start_step')->default(false);
            $table->boolean('is_end_step')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_steps');
    }
};