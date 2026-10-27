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
        Schema::create('workflow_rules', function (Blueprint $table) {
            $table->id();
            $table->string('workflow_type')->comment('e.g., Leave Request, Procurement');
            $table->unsignedBigInteger('department_id')->nullable();
            $table->string('rule_key')->comment('e.g., approval_threshold, skip_levels');
            $table->text('rule_value')->comment('JSON or plain text value');
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_rules');
    }
};
