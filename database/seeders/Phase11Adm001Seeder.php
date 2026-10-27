<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Workflow;
use App\Models\WorkflowVersion;
use App\Models\WorkflowStep;
use App\Models\WorkflowTransition;
use App\Models\WorkflowChecklist;
use App\Models\Department;
use App\Models\User;

class Phase11Adm001Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get Super Admin User ID for created_by
        $superAdmin = User::whereHas('roles', function($q) {
            $q->where('name', 'super admin');
        })->first();
        $adminId = $superAdmin ? $superAdmin->id : 1;

        $adminDept = \App\Models\Department::where('title', 'Administration')->first();
        $adminDeptId = $adminDept ? $adminDept->id : null;

        // 1. Create Workflow
        $workflow = Workflow::firstOrCreate(
            ['title' => 'ADM-001 General Administration Correspondence'],
            [
                'created_by' => $adminId,
                'status' => 'active',
                'emoji' => '📁'
            ]
        );

        // 2. Create Workflow Version
        $version = WorkflowVersion::firstOrCreate(
            ['workflow_id' => $workflow->id, 'version_number' => 1],
            [
                'is_published' => true,
                'published_at' => now(),
                'mandate_parallel_splits' => false
            ]
        );

        // 3. Create Steps
        $step1 = WorkflowStep::firstOrCreate(
            ['workflow_version_id' => $version->id, 'name' => 'AGF Registry Receipt & Registration'],
            [
                'step_type' => 'input',
                'role_required' => 'registry',
                'is_start_step' => true,
                'is_end_step' => false
            ]
        );

        $step2 = WorkflowStep::firstOrCreate(
            ['workflow_version_id' => $version->id, 'name' => 'AGF Direction'],
            [
                'step_type' => 'approval',
                'role_required' => 'Accountant-General',
                'is_start_step' => false,
                'is_end_step' => false
            ]
        );

        $step3 = WorkflowStep::firstOrCreate(
            ['workflow_version_id' => $version->id, 'name' => 'DA Registry Receipt'],
            [
                'step_type' => 'input',
                'role_required' => 'registry',
                'is_start_step' => false,
                'is_end_step' => false
            ]
        );

        $step4 = WorkflowStep::firstOrCreate(
            ['workflow_version_id' => $version->id, 'name' => 'DA Review'],
            [
                'step_type' => 'review',
                'role_required' => 'directors',
                'department_id' => $adminDeptId,
                'is_start_step' => false,
                'is_end_step' => false
            ]
        );

        $step5 = WorkflowStep::firstOrCreate(
            ['workflow_version_id' => $version->id, 'name' => 'Action Officer Processing'],
            [
                'step_type' => 'input',
                'role_required' => null, // Dynamic routing
                'is_start_step' => false,
                'is_end_step' => false
            ]
        );

        $step6 = WorkflowStep::firstOrCreate(
            ['workflow_version_id' => $version->id, 'name' => 'Dispatch and Closure'],
            [
                'step_type' => 'approval',
                'role_required' => 'registry',
                'is_start_step' => false,
                'is_end_step' => true
            ]
        );

        // 4. Create Checklists
        $this->syncChecklists($step1, [
            "Check correspondence",
            "Date-stamp correspondence",
            "Register correspondence",
            "File correspondence"
        ]);

        $this->syncChecklists($step2, [
            "Review and Provide Direction"
        ]);

        $this->syncChecklists($step3, [
            "Receive correspondence",
            "Register correspondence"
        ]);

        $this->syncChecklists($step4, [
            "Review correspondence",
            "Determine routing"
        ]);

        $this->syncChecklists($step5, [
            "Process action",
            "Provide feedback"
        ]);

        $this->syncChecklists($step6, [
            "Dispatch response",
            "Close file"
        ]);

        // 5. Create Transitions
        WorkflowTransition::firstOrCreate([
            'workflow_version_id' => $version->id,
            'from_step_id' => $step1->id,
            'to_step_id' => $step2->id
        ], ['action_name' => 'Submit to AGF']);

        WorkflowTransition::firstOrCreate([
            'workflow_version_id' => $version->id,
            'from_step_id' => $step2->id,
            'to_step_id' => $step3->id
        ], ['action_name' => 'Route to DA Registry']);

        WorkflowTransition::firstOrCreate([
            'workflow_version_id' => $version->id,
            'from_step_id' => $step3->id,
            'to_step_id' => $step4->id
        ], ['action_name' => 'Forward for Review']);

        WorkflowTransition::firstOrCreate([
            'workflow_version_id' => $version->id,
            'from_step_id' => $step4->id,
            'to_step_id' => $step5->id
        ], ['action_name' => 'Route to Action Officer']);

        WorkflowTransition::firstOrCreate([
            'workflow_version_id' => $version->id,
            'from_step_id' => $step5->id,
            'to_step_id' => $step4->id
        ], ['action_name' => 'Return to DA (Upward Processing)']);

        WorkflowTransition::firstOrCreate([
            'workflow_version_id' => $version->id,
            'from_step_id' => $step4->id,
            'to_step_id' => $step6->id
        ], ['action_name' => 'Forward for Dispatch']);

        // AGF direct closure option
        WorkflowTransition::firstOrCreate([
            'workflow_version_id' => $version->id,
            'from_step_id' => $step2->id,
            'to_step_id' => $step6->id
        ], ['action_name' => 'Direct Dispatch']);

        $this->command->info('Phase 11 ADM-001 Workflow created successfully.');
    }

    private function syncChecklists($step, $items) {
        $checklists = [];
        foreach ($items as $item) {
            $cl = WorkflowChecklist::firstOrCreate(
                ['workflow_step_id' => $step->id, 'item_description' => $item],
                ['is_required' => true]
            );
            $checklists[] = [
                'id' => (string) $cl->id, // Fake UUID string id for UI if needed, but DB stores id
                'item_text' => $item,
                'is_required' => true
            ];
        }
        
        // Ensure form_schema holds the checklists array for UI compatibility if needed
        $schema = ['checklists' => $checklists];
        $step->update(['form_schema' => json_encode($schema)]);
    }
}
