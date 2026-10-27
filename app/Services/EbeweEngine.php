<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

class EbeweEngine
{
    /**
     * Define the strict order of hierarchy.
     */
    protected const HIERARCHY = [
        'accountant_general' => 1,
        'special_adviser' => 2,
        'chief_of_staff' => 3,
        'director' => 4,
        'deputy_director' => 5,
        'assistant_director' => 6,
        'chief_officer' => 7,
        'assistant_chief' => 8,
        'principal_officer_i' => 9,
        'principal_officer_ii' => 10,
        'senior_officer' => 11,
        'officer' => 12
    ];

    /**
     * Advance the document to the next logical step in the EBEWE unified engine.
     */
    public function advance($instanceId, User $actionBy, $comments = '', $targetUserId = null)
    {
        return DB::transaction(function () use ($instanceId, $actionBy, $comments, $targetUserId) {
            $instance = DB::table('ebewe_instances')->where('id', $instanceId)->first();
            
            if (!$instance) {
                throw new Exception("EBEWE Instance not found.");
            }

            if ($instance->status === 'completed') {
                throw new Exception("This workflow instance is already completed.");
            }

            // Get current stage
            $currentStage = DB::table('ebewe_stages')->where('id', $instance->current_stage_id)->first();

            // Determine next stage
            $nextStage = null;
            if ($currentStage) {
                $nextStage = DB::table('ebewe_stages')
                    ->where('workflow_id', $instance->workflow_id)
                    ->where('step_order', '>', $currentStage->step_order)
                    ->orderBy('step_order', 'asc')
                    ->first();
            } else {
                // First stage
                $nextStage = DB::table('ebewe_stages')
                    ->where('workflow_id', $instance->workflow_id)
                    ->orderBy('step_order', 'asc')
                    ->first();
            }

            if (!$nextStage) {
                // Reached the end of the workflow map
                DB::table('ebewe_instances')->where('id', $instance->id)->update([
                    'status' => 'completed',
                    'updated_at' => now(),
                ]);

                DB::table('ebewe_transitions')->insert([
                    'instance_id' => $instance->id,
                    'stage_id' => $currentStage ? $currentStage->id : null,
                    'action_by' => $actionBy->id,
                    'action_to' => null,
                    'action_type' => 'completed',
                    'comments' => 'Workflow Completed: ' . $comments,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Optional: If this is a Payment, mark the payment as 'paid'
                if ($instance->document_type === 'App\\Models\\Payment') {
                    DB::table('payments')->where('id', $instance->document_id)->update(['status' => 'paid']);
                }

                return true;
            }

            // Determine routing logic based on the Stage Configuration
            $routeType = $nextStage->routing_type;
            $nextUserId = $targetUserId; // Can be explicitly passed in UI or inferred

            // Infer next user if not explicitly provided
            if (!$nextUserId) {
                if ($routeType === 'LATERAL' || $routeType === 'LINEAR') {
                    // Logic to find a user matching the destination role/dept
                    $destinationRole = $nextStage->destination;
                    // For now, if no explicit user is passed, we throw an error requiring UI to provide one
                    throw new Exception("Explicit target user ID required for {$routeType} routing to {$destinationRole}.");
                }
            }

            // Update Instance
            DB::table('ebewe_instances')->where('id', $instance->id)->update([
                'current_stage_id' => $nextStage->id,
                'current_assigned_to' => $nextUserId,
                'assigned_by' => $actionBy->id,
                'updated_at' => now(),
            ]);

            // Log Transition
            DB::table('ebewe_transitions')->insert([
                'instance_id' => $instance->id,
                'stage_id' => $nextStage->id,
                'action_by' => $actionBy->id,
                'action_to' => $nextUserId,
                'action_type' => 'assigned',
                'comments' => $comments,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Optional: If this is a Payment, update its status based on the routing (for legacy dashboard support)
            if ($instance->document_type === 'App\\Models\\Payment') {
                DB::table('payments')->where('id', $instance->document_id)->update(['status' => 'due']);
            }

            return $nextStage;
        });
    }

    /**
     * Return document back to the previous handler.
     */
    public function returnDocument($instanceId, User $actionBy, $comments = '')
    {
        return DB::transaction(function () use ($instanceId, $actionBy, $comments) {
            $instance = DB::table('ebewe_instances')->where('id', $instanceId)->first();
            
            // Find who sent it to the current user
            $lastTransition = DB::table('ebewe_transitions')
                ->where('instance_id', $instanceId)
                ->where('action_to', $actionBy->id)
                ->latest()
                ->first();

            if (!$lastTransition || !$lastTransition->action_by) {
                throw new Exception("Cannot return document. No previous handler found.");
            }

            // Reassign to the sender
            DB::table('ebewe_instances')->where('id', $instance->id)->update([
                'current_assigned_to' => $lastTransition->action_by,
                'assigned_by' => $actionBy->id,
                'status' => 'returned',
                'updated_at' => now(),
            ]);

            // Log rejection
            DB::table('ebewe_transitions')->insert([
                'instance_id' => $instance->id,
                'stage_id' => $instance->current_stage_id,
                'action_by' => $actionBy->id,
                'action_to' => $lastTransition->action_by,
                'action_type' => 'returned',
                'comments' => $comments,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($instance->document_type === 'App\\Models\\Payment') {
                DB::table('payments')->where('id', $instance->document_id)->update(['status' => 'reverted']);
            }

            return true;
        });
    }
}
