<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use App\Models\WorkflowTransition;
use App\Models\WorkflowSla;

class Phase13Adm004Seeder extends Seeder
{
    public function run()
    {
        $adminDept = Department::where('title', 'Administration')->first();

        if (!$adminDept) {
            $this->command->error('Administration department not found.');
            return;
        }

        // Create ADM-004 Confidential Discipline Workflow
        $workflow = Workflow::firstOrCreate([
            'title' => 'ADM-004 Discipline Workflow',
            'department_id' => $adminDept->id,
            'is_confidential' => true
        ], [
            'emoji' => '⚖️',
            'created_by' => 1
        ]);

        $version = $workflow->versions()->firstOrCreate([
            'version_number' => 1,
            'is_published' => true
        ]);

        // Define Steps
        $step1 = WorkflowStep::firstOrCreate([
            'workflow_version_id' => $version->id,
            'name' => 'Incident Report Received',
            'step_type' => 'input',
            'is_start_step' => true,
            'department_id' => $adminDept->id,
            'role_required' => 'assistant director' // AD Appointment
        ]);

        $step2 = WorkflowStep::firstOrCreate([
            'workflow_version_id' => $version->id,
            'name' => 'Preliminary Investigation',
            'step_type' => 'review',
            'department_id' => $adminDept->id,
            'role_required' => 'assistant director'
        ]);

        $step3 = WorkflowStep::firstOrCreate([
            'workflow_version_id' => $version->id,
            'name' => 'Issue Query',
            'step_type' => 'approval',
            'department_id' => $adminDept->id,
            'role_required' => 'deputy director' // DD APD
        ]);

        $step4 = WorkflowStep::firstOrCreate([
            'workflow_version_id' => $version->id,
            'name' => 'Staff Answers Query',
            'step_type' => 'input',
            'department_id' => $adminDept->id,
        ]);

        $step5 = WorkflowStep::firstOrCreate([
            'workflow_version_id' => $version->id,
            'name' => 'Disciplinary Panel',
            'step_type' => 'review',
            'department_id' => $adminDept->id,
        ]);

        $step6 = WorkflowStep::firstOrCreate([
            'workflow_version_id' => $version->id,
            'name' => 'Panel Report to DD APD / DA',
            'step_type' => 'approval',
            'department_id' => $adminDept->id,
            'role_required' => 'directors'
        ]);

        $step7 = WorkflowStep::firstOrCreate([
            'workflow_version_id' => $version->id,
            'name' => 'Final Decision (AGF)',
            'step_type' => 'approval',
            'department_id' => $adminDept->id,
            'role_required' => 'agf'
        ]);

        $step8 = WorkflowStep::firstOrCreate([
            'workflow_version_id' => $version->id,
            'name' => 'Communicate Decision',
            'step_type' => 'input',
            'is_end_step' => true,
            'department_id' => $adminDept->id,
            'role_required' => 'assistant director'
        ]);

        // SLAs based on SOP
        WorkflowSla::updateOrCreate(['workflow_step_id' => $step1->id], ['duration_hours' => 0]); // Day 0
        WorkflowSla::updateOrCreate(['workflow_step_id' => $step2->id], ['duration_hours' => 3 * 24]); // Day 1-3
        WorkflowSla::updateOrCreate(['workflow_step_id' => $step3->id], ['duration_hours' => 2 * 24]); // Day 5
        WorkflowSla::updateOrCreate(['workflow_step_id' => $step4->id], ['duration_hours' => 4 * 24]); // Day 9 (72hrs from Day 5)
        WorkflowSla::updateOrCreate(['workflow_step_id' => $step5->id], ['duration_hours' => 11 * 24]); // Day 10-21
        WorkflowSla::updateOrCreate(['workflow_step_id' => $step6->id], ['duration_hours' => 9 * 24]); // Day 30
        WorkflowSla::updateOrCreate(['workflow_step_id' => $step7->id], ['duration_hours' => 10 * 24]); // Within Day 45
        WorkflowSla::updateOrCreate(['workflow_step_id' => $step8->id], ['duration_hours' => 5 * 24]); // By Day 45

        // Transitions
        WorkflowTransition::firstOrCreate([
            'workflow_version_id' => $version->id,
            'from_step_id' => $step1->id,
            'to_step_id' => $step2->id,
            'action_name' => 'Begin Investigation'
        ]);

        WorkflowTransition::firstOrCreate([
            'workflow_version_id' => $version->id,
            'from_step_id' => $step2->id,
            'to_step_id' => $step3->id,
            'action_name' => 'Request Query Issuance'
        ]);

        // Alternative to close it if no query needed
        WorkflowTransition::firstOrCreate([
            'workflow_version_id' => $version->id,
            'from_step_id' => $step2->id,
            'to_step_id' => $step8->id,
            'action_name' => 'Dismiss Case / Exonerate'
        ]);

        WorkflowTransition::firstOrCreate([
            'workflow_version_id' => $version->id,
            'from_step_id' => $step3->id,
            'to_step_id' => $step4->id,
            'action_name' => 'Query Issued'
        ]);

        WorkflowTransition::firstOrCreate([
            'workflow_version_id' => $version->id,
            'from_step_id' => $step4->id,
            'to_step_id' => $step5->id,
            'action_name' => 'Response Unsatisfactory - Set Up Panel'
        ]);
        
        WorkflowTransition::firstOrCreate([
            'workflow_version_id' => $version->id,
            'from_step_id' => $step4->id,
            'to_step_id' => $step8->id,
            'action_name' => 'Response Satisfactory - Close'
        ]);

        WorkflowTransition::firstOrCreate([
            'workflow_version_id' => $version->id,
            'from_step_id' => $step5->id,
            'to_step_id' => $step6->id,
            'action_name' => 'Submit Panel Report'
        ]);

        WorkflowTransition::firstOrCreate([
            'workflow_version_id' => $version->id,
            'from_step_id' => $step6->id,
            'to_step_id' => $step7->id,
            'action_name' => 'Forward to AGF'
        ]);

        WorkflowTransition::firstOrCreate([
            'workflow_version_id' => $version->id,
            'from_step_id' => $step7->id,
            'to_step_id' => $step8->id,
            'action_name' => 'Approve Decision'
        ]);

        $this->command->info('Phase 13 ADM-004 Discipline Workflow seeded successfully.');
    }
}
