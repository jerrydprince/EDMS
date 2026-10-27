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
        Schema::create('ebewe_stamps', function (Blueprint $table) {
            $table->id();
            
            // Link to the EBEWE instance (which points to the document/payment)
            $table->foreignId('instance_id')->constrained('ebewe_instances')->onDelete('cascade');
            
            // The stage where this stamp was applied (e.g. Audit Stage)
            $table->foreignId('stage_id')->nullable()->constrained('ebewe_stages')->onDelete('set null');
            
            // Who applied the stamp
            $table->unsignedBigInteger('applied_by'); 
            
            // Stamp details
            $table->string('stamp_type'); // e.g., 'checking', 'audit', 'final_accounts'
            $table->string('label'); // e.g., 'PASSED FOR PAYMENT', 'AUDITED AND FOUND CORRECT'
            $table->string('color')->default('red'); // visual style metadata
            $table->string('design_pattern')->default('circular'); // visual style metadata
            $table->text('comments')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ebewe_stamps');
    }
};
