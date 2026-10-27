<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Workflow;
use App\Models\WorkflowVersion;
use App\Models\WorkflowStep;

class Phase17Adm008Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. ADM-008 Redeployment
        $workflowRedep = Workflow::firstOrCreate(
            ['title' => 'ADM-008 Redeployment'],
            ['status' => 'published', 'created_by' => 1]
        );

        $schemaRedep = [
            ['name' => 'mda_name', 'label' => 'New MDA', 'type' => 'text', 'required' => true],
            ['name' => 'target_user_id', 'label' => 'Staff ID', 'type' => 'number', 'required' => true],
            ['name' => 'assumption_date', 'label' => 'Assumption of Duty Date', 'type' => 'date', 'required' => false]
        ];

        $versionRedep = WorkflowVersion::firstOrCreate(
            ['workflow_id' => $workflowRedep->id, 'version_number' => 1],
            ['is_published' => true, 'form_schema' => $schemaRedep]
        );

        WorkflowStep::firstOrCreate([
            'workflow_version_id' => $versionRedep->id,
            'name' => 'Redeployment Process',
            'step_type' => 'approval',
            'role_required' => 'super admin',
            'is_start_step' => true,
            'is_end_step' => true
        ]);

        // 2. ADM-008 Recall
        $workflowRecall = Workflow::firstOrCreate(
            ['title' => 'ADM-008 Recall'],
            ['status' => 'published', 'created_by' => 1]
        );

        $schemaRecall = [
            ['name' => 'target_user_id', 'label' => 'Staff ID to Recall', 'type' => 'number', 'required' => true],
            ['name' => 'reason_for_recall', 'label' => 'Reason', 'type' => 'textarea', 'required' => true]
        ];

        $versionRecall = WorkflowVersion::firstOrCreate(
            ['workflow_id' => $workflowRecall->id, 'version_number' => 1],
            ['is_published' => true, 'form_schema' => $schemaRecall]
        );

        WorkflowStep::firstOrCreate([
            'workflow_version_id' => $versionRecall->id,
            'name' => 'Recall Process',
            'step_type' => 'approval',
            'role_required' => 'super admin',
            'is_start_step' => true,
            'is_end_step' => true
        ]);

        // 3. ADM-008 Replacement
        $workflowRep = Workflow::firstOrCreate(
            ['title' => 'ADM-008 Replacement'],
            ['status' => 'published', 'created_by' => 1]
        );

        $schemaRep = [
            ['name' => 'target_user_id', 'label' => 'Incoming Staff ID', 'type' => 'number', 'required' => true],
            ['name' => 'replaced_user_id', 'label' => 'Replaced Staff ID (Recalled)', 'type' => 'number', 'required' => true],
            ['name' => 'mda_name', 'label' => 'MDA Name', 'type' => 'text', 'required' => true],
            ['name' => 'assumption_date', 'label' => 'Assumption of Duty Date', 'type' => 'date', 'required' => false]
        ];

        $versionRep = WorkflowVersion::firstOrCreate(
            ['workflow_id' => $workflowRep->id, 'version_number' => 1],
            ['is_published' => true, 'form_schema' => $schemaRep]
        );

        WorkflowStep::firstOrCreate([
            'workflow_version_id' => $versionRep->id,
            'name' => 'Replacement Process',
            'step_type' => 'approval',
            'role_required' => 'super admin',
            'is_start_step' => true,
            'is_end_step' => true
        ]);
    }
}
