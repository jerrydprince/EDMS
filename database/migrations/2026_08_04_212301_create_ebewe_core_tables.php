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
        // 1. Workflow Definitions (Templates)
        Schema::create('ebewe_workflows', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('module'); // e.g. 'Payment Vouchers', 'External Mails'
            $table->text('description')->nullable();
            $table->string('status')->default('active'); // active, inactive
            $table->timestamps();
        });

        // 2. Stage Configurations (The Routing Logic)
        Schema::create('ebewe_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('ebewe_workflows')->onDelete('cascade');
            $table->string('name'); // e.g. 'Initial Briefing'
            $table->integer('step_order'); // 1, 2, 3...
            $table->string('routing_type'); // TOP_DOWN, BOTTOM_UP, LATERAL, PARALLEL, LINEAR
            $table->string('source')->nullable(); // Role or Dept starting this stage
            $table->string('destination')->nullable(); // Target Role or Dept
            $table->json('rules')->nullable(); // e.g. {"minAmount": 50000000}
            $table->timestamps();
        });

        // 3. Workflow Instances (Active Documents being routed)
        Schema::create('ebewe_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('ebewe_workflows')->onDelete('cascade');
            
            // Polymorphic relation to the actual document (e.g., Payment, FinanceFiles)
            $table->string('document_type'); // 'App\Models\Payment'
            $table->unsignedBigInteger('document_id'); // 1
            
            $table->foreignId('current_stage_id')->nullable()->constrained('ebewe_stages')->onDelete('set null');
            
            // Current assignment tracking (who currently has it?)
            $table->unsignedBigInteger('current_assigned_to')->nullable(); // user_id
            $table->unsignedBigInteger('assigned_by')->nullable(); // user_id
            
            $table->string('status')->default('pending'); // pending, approved, rejected, completed
            $table->timestamps();
        });

        // 4. Transitions/Audit Log (The bidirectional history)
        Schema::create('ebewe_transitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instance_id')->constrained('ebewe_instances')->onDelete('cascade');
            $table->foreignId('stage_id')->nullable()->constrained('ebewe_stages')->onDelete('set null');
            
            $table->unsignedBigInteger('action_by'); // user_id
            $table->unsignedBigInteger('action_to')->nullable(); // user_id
            
            $table->string('action_type'); // delegated, submitted, approved, returned, stamped
            $table->text('comments')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ebewe_transitions');
        Schema::dropIfExists('ebewe_instances');
        Schema::dropIfExists('ebewe_stages');
        Schema::dropIfExists('ebewe_workflows');
    }
};
