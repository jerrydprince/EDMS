<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateLegacyEbeweWorkflows extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ebewe:migrate-legacy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate legacy payment assignments and workflows into the unified EBEWE tables.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting EBEWE Legacy Migration...");

        DB::transaction(function () {
            // 1. Create a placeholder Workflow for Legacy Payments
            $legacyWorkflowId = DB::table('ebewe_workflows')->insertGetId([
                'name' => 'Legacy F&A Payment Workflow',
                'module' => 'Payment Vouchers',
                'description' => 'Auto-generated workflow for migrating legacy payment assignments.',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->info("Created Legacy Workflow template (ID: {$legacyWorkflowId})");

            // 2. Fetch all unique payments
            $payments = DB::table('payments')->get();
            $this->info("Found " . count($payments) . " legacy payments to migrate.");

            foreach ($payments as $payment) {
                // Determine current assignment from payment_assignment table
                $latestAssignment = DB::table('payment_assignment')
                    ->where('payment_id', $payment->id)
                    ->orderBy('id', 'desc')
                    ->first();

                $assignedTo = $latestAssignment ? $latestAssignment->recipient_id : null;
                $assignedBy = $latestAssignment ? $latestAssignment->assigned_by : null;

                // Create the EBEWE Instance
                $instanceId = DB::table('ebewe_instances')->insertGetId([
                    'workflow_id' => $legacyWorkflowId,
                    'document_type' => 'App\\Models\\Payment',
                    'document_id' => $payment->id,
                    'current_stage_id' => null, // Legacy didn't use stages
                    'current_assigned_to' => $assignedTo,
                    'assigned_by' => $assignedBy,
                    'status' => $payment->status === 'paid' ? 'completed' : 'pending',
                    'created_at' => $payment->created_at ?? now(),
                    'updated_at' => $payment->updated_at ?? now(),
                ]);

                // Create transitions for all assignments related to this payment
                $assignments = DB::table('payment_assignment')
                    ->where('payment_id', $payment->id)
                    ->orderBy('id', 'asc')
                    ->get();

                foreach ($assignments as $assignment) {
                    DB::table('ebewe_transitions')->insert([
                        'instance_id' => $instanceId,
                        'stage_id' => null,
                        'action_by' => $assignment->assigned_by,
                        'action_to' => $assignment->recipient_id,
                        'action_type' => $assignment->status === 'done' ? 'submitted' : 'delegated',
                        'comments' => $assignment->comments,
                        'created_at' => $assignment->created_at ?? now(),
                        'updated_at' => $assignment->updated_at ?? now(),
                    ]);
                }
            }

            $this->info("Successfully migrated F&A Payment assignments into EBEWE instances and transitions.");
            
            // TODO: In the future, we can add logic here to also migrate WorkflowRoute 
            // and WorkflowLog data for non-payment tasks.
        });

        $this->info("EBEWE Migration Complete!");
    }
}
