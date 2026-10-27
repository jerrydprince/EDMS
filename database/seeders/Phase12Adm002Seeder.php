<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Workflow;
use App\Models\WorkflowVersion;
use App\Models\WorkflowStep;
use App\Models\WorkflowTransition;
use App\Models\WorkflowChecklist;
use Illuminate\Support\Str;

class Phase12Adm002Seeder extends Seeder
{
    public function run()
    {
        $adminDept = \App\Models\Department::where('title', 'Administration')->first();
        $adminDeptId = $adminDept ? $adminDept->id : null;

        $variants = [
            'ADM-002-1 Assumption of Duty',
            'ADM-002-2 Appointment Documentation',
            'ADM-002-3 Confirmation of Appointment',
            'ADM-002-4 Regularisation',
            'ADM-002-5 Leave Processing',
            'ADM-002-6 Secondment',
            'ADM-002-7 Transfer of Service',
            'ADM-002-8 Acting Appointment',
            'ADM-002-9 Death/Retirement Documentation'
        ];

        foreach ($variants as $variantName) {
            $workflow = Workflow::firstOrCreate(
                ['title' => $variantName],
                [
                    'emoji' => '📄',
                    'department_id' => $adminDeptId,
                    'created_by' => 1
                ]
            );

            $version = WorkflowVersion::firstOrCreate(
                ['workflow_id' => $workflow->id, 'version_number' => 1],
                [
                    'is_published' => true,
                    'published_at' => now(),
                    'mandate_parallel_splits' => false
                ]
            );

            // If steps exist, skip seeding to avoid duplicate
            if (WorkflowStep::where('workflow_version_id', $version->id)->exists()) {
                continue;
            }

            // Step 1: AD Appointment Registry Receipt
            $step1 = WorkflowStep::create([
                'workflow_version_id' => $version->id,
                'name' => 'AD Appointment Registry Receipt',
                'step_type' => 'input',
                'role_required' => 'registry',
                'is_start_step' => true,
                'is_end_step' => false,
            ]);
            $this->addChecklists($step1, ['Log request', 'Check Enclosures', 'Open/retrieve file', 'Stamp DATE RECEIVED']);

            // Step 2: Schedule Officer Examination
            $step2 = WorkflowStep::create([
                'workflow_version_id' => $version->id,
                'name' => 'Schedule Officer Examination',
                'step_type' => 'review',
                'role_required' => null, // Dynamic Schedule Officer
                'is_start_step' => false,
                'is_end_step' => false,
            ]);
            $this->addChecklists($step2, ['Examine file', 'Check PSR provision', 'Check precedent', 'Minute with PSR citation']);

            // Step 3: AD Appointment Review
            $step3 = WorkflowStep::create([
                'workflow_version_id' => $version->id,
                'name' => 'AD Appointment Review',
                'step_type' => 'approval',
                'role_required' => 'Assistant Director (Appointment)',
                'is_start_step' => false,
                'is_end_step' => false,
            ]);
            $this->addChecklists($step3, ['Review minute', 'Sign recommendation / Vetting']);

            // Step 4: DD APD Registry Receipt
            $step4 = WorkflowStep::create([
                'workflow_version_id' => $version->id,
                'name' => 'DD APD Registry Receipt',
                'step_type' => 'input',
                'role_required' => 'registry',
                'is_start_step' => false,
                'is_end_step' => false,
            ]);
            $this->addChecklists($step4, ['Log into Inward Register APD', 'Submit to DD APD']);

            // Step 5: DD APD Review
            $step5 = WorkflowStep::create([
                'workflow_version_id' => $version->id,
                'name' => 'DD APD Review',
                'step_type' => 'approval',
                'role_required' => 'Deputy Director (APD)',
                'is_start_step' => false,
                'is_end_step' => false,
            ]);
            $this->addChecklists($step5, ['Review AD recommendations against PSR', 'Provide recommendation to DA', 'Forward to DA with covering memo']);

            // Step 6: DA Approval
            $step6 = WorkflowStep::create([
                'workflow_version_id' => $version->id,
                'name' => 'DA Approval',
                'step_type' => 'approval',
                'role_required' => 'Director Administration (DA)',
                'is_start_step' => false,
                'is_end_step' => false,
            ]);
            $this->addChecklists($step6, ['Review DD APD recommendation', 'Approve within delegated powers OR forward to AGF']);

            // Step 7: Final Dispatch and File Update
            $step7 = WorkflowStep::create([
                'workflow_version_id' => $version->id,
                'name' => 'Dispatch and Update IPPIS/HR',
                'step_type' => 'input',
                'role_required' => 'registry',
                'is_start_step' => false,
                'is_end_step' => true,
            ]);
            $this->addChecklists($step7, ['Issue approval letter', 'Update personnel file', 'Update IPPIS/HR Module']);

            // Extra specific steps for Confirmation of Appointment
            if ($variantName === 'ADM-002-3 Confirmation of Appointment') {
                $stepAGF = WorkflowStep::create([
                    'workflow_version_id' => $version->id,
                    'name' => 'AGF Noting',
                    'step_type' => 'approval',
                    'role_required' => 'Accountant-General',
                    'is_start_step' => false,
                    'is_end_step' => false,
                ]);
                $this->addChecklists($stepAGF, ['Note confirmation', 'Direct notification to FCSC']);

                // Transitions for Confirmation
                $this->addTransition($version, $step1, $step2, 'Route to Schedule Officer');
                $this->addTransition($version, $step2, $step3, 'Submit to AD Appointment');
                $this->addTransition($version, $step3, $step4, 'Forward to DD APD Registry');
                $this->addTransition($version, $step4, $step5, 'Submit to DD APD');
                $this->addTransition($version, $step5, $step6, 'Forward to DA');
                $this->addTransition($version, $step6, $stepAGF, 'Forward to AGF');
                $this->addTransition($version, $stepAGF, $step7, 'Return for Dispatch');
            } else {
                // Standard Transitions
                $this->addTransition($version, $step1, $step2, 'Route to Schedule Officer');
                $this->addTransition($version, $step2, $step3, 'Submit to AD Appointment');
                $this->addTransition($version, $step3, $step4, 'Forward to DD APD Registry');
                $this->addTransition($version, $step4, $step5, 'Submit to DD APD');
                $this->addTransition($version, $step5, $step6, 'Forward to DA');
                $this->addTransition($version, $step6, $step7, 'Return to AD for Dispatch');
                $this->addTransition($version, $step6, $step5, 'Return to DD APD with observations'); // Downward processing
            }
        }

        $this->command->info('Phase 12 ADM-002 Appointment Variants seeded successfully.');
    }

    private function addChecklists($step, $items)
    {
        foreach ($items as $item) {
            WorkflowChecklist::create([
                'workflow_step_id' => $step->id,
                'item_description' => $item,
                'is_required' => true,
            ]);
        }
    }

    private function addTransition($version, $from, $to, $actionName)
    {
        WorkflowTransition::create([
            'workflow_version_id' => $version->id,
            'from_step_id' => $from->id,
            'to_step_id' => $to->id,
            'action_name' => $actionName,
        ]);
    }
}
