<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Workflow;
use App\Models\WorkflowVersion;
use App\Models\WorkflowStep;
use App\Models\WorkflowSla;

class Phase16Adm007Seeder extends Seeder
{
    public function run(): void
    {
        $workflow = Workflow::firstOrCreate(
            ['title' => 'ADM-007 Pool Posting'],
            ['status' => 'published', 'created_by' => 1]
        );

        $schema = [
            ['name' => 'mda_name', 'label' => 'Requisitioning MDA', 'type' => 'text', 'required' => true],
            ['name' => 'required_cadre', 'label' => 'Required Cadre / Specialization', 'type' => 'text', 'required' => true],
            ['name' => 'target_user_id', 'label' => 'Matched User ID', 'type' => 'number', 'required' => false],
            ['name' => 'assumption_date', 'label' => 'Assumption of Duty Date', 'type' => 'date', 'required' => false]
        ];

        $version = WorkflowVersion::firstOrCreate(
            ['workflow_id' => $workflow->id, 'version_number' => 1],
            ['is_published' => true, 'form_schema' => $schema]
        );

        $step1 = WorkflowStep::firstOrCreate([
            'workflow_version_id' => $version->id,
            'name' => 'MDA Requisition',
            'step_type' => 'approval',
            'role_required' => 'admin',
            'is_start_step' => true,
            'is_end_step' => false
        ]);

        $step2 = WorkflowStep::firstOrCreate([
            'workflow_version_id' => $version->id,
            'name' => 'Analysis & Matching',
            'step_type' => 'approval',
            'role_required' => 'super admin',
            'is_start_step' => false,
            'is_end_step' => false
        ]);

        $step3 = WorkflowStep::firstOrCreate([
            'workflow_version_id' => $version->id,
            'name' => 'DA Approval',
            'step_type' => 'approval',
            'role_required' => 'super admin',
            'is_start_step' => false,
            'is_end_step' => true
        ]);
    }
}
