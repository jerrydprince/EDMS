<?php

namespace App\Traits;

use App\Services\EbeweEngine;
use Illuminate\Support\Facades\DB;

trait HasEbeweWorkflow
{
    /**
     * Start an EBEWE Workflow for this document.
     */
    public function startEbeweWorkflow($workflowId, $user, $comments = 'Document Initialized')
    {
        return DB::transaction(function () use ($workflowId, $user, $comments) {
            $workflow = DB::table('ebewe_workflows')->where('id', $workflowId)->first();
            if (!$workflow) {
                throw new \Exception("Workflow ID {$workflowId} not found.");
            }

            // Create the EBEWE Instance polymorphic link
            $instanceId = DB::table('ebewe_instances')->insertGetId([
                'workflow_id' => $workflowId,
                'document_type' => static::class, // e.g., 'App\Models\RegistryFile'
                'document_id' => $this->id,
                'current_assigned_to' => $user->id,
                'assigned_by' => $user->id,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Auto-advance to the first stage
            $ebewe = new EbeweEngine();
            return $ebewe->advance($instanceId, $user, $comments);
        });
    }

    /**
     * Advance this document through the EBEWE Engine.
     */
    public function advanceWorkflow($user, $comments = '', $targetUserId = null)
    {
        $instance = $this->getEbeweInstance();
        if (!$instance) {
            throw new \Exception("No EBEWE workflow is active for this document.");
        }

        $ebewe = new EbeweEngine();
        return $ebewe->advance($instance->id, $user, $comments, $targetUserId);
    }

    /**
     * Return this document backward in the workflow.
     */
    public function returnWorkflow($user, $comments = '')
    {
        $instance = $this->getEbeweInstance();
        if (!$instance) {
            throw new \Exception("No EBEWE workflow is active for this document.");
        }

        $ebewe = new EbeweEngine();
        return $ebewe->returnDocument($instance->id, $user, $comments);
    }

    /**
     * Get the active EBEWE Instance for this document.
     */
    public function getEbeweInstance()
    {
        return DB::table('ebewe_instances')
            ->where('document_type', static::class)
            ->where('document_id', $this->id)
            ->first();
    }

    /**
     * Get the EBEWE Audit Log / Transitions History.
     */
    public function getEbeweHistory()
    {
        $instance = $this->getEbeweInstance();
        if (!$instance) return [];

        return DB::table('ebewe_transitions')
            ->where('instance_id', $instance->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get affixed digital stamps for this document.
     */
    public function getEbeweStamps()
    {
        $instance = $this->getEbeweInstance();
        if (!$instance) return [];

        return DB::table('ebewe_stamps')
            ->where('instance_id', $instance->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
