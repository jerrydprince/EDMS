<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Workflow;
use App\Models\WorkflowVersion;
use App\Models\WorkflowStep;
use App\Models\WorkflowTransition;
use App\Models\WorkflowChecklist;
use App\Models\WorkflowSla;
use App\Models\WorkflowRule;
use App\Models\User;

class MasterRoutingWorkflowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $superAdmin = User::whereHas('roles', function($q) {
            $q->where('name', 'super admin');
        })->first();
        $adminId = $superAdmin ? $superAdmin->id : 1;

        // 1. Create or Update "Master Routing Workflow"
        $workflow = Workflow::updateOrCreate(
            ['title' => 'Master Routing Workflow'],
            [
                'created_by' => $adminId,
                'status' => 'active',
                'emoji' => '🏛️',
                'is_confidential' => false
            ]
        );

        // 2. Create Published Workflow Version 1
        $version = WorkflowVersion::updateOrCreate(
            ['workflow_id' => $workflow->id, 'version_number' => 1],
            [
                'is_published' => true,
                'is_approved' => true,
                'approved_by_id' => $adminId,
                'approved_at' => now(),
                'published_at' => now(),
                'mandate_parallel_splits' => false
            ]
        );

        // 3. Define the 6 Steps of the Hub-and-Spoke Lifecycle
        // Step 1: Central Registry Intake
        $step1 = WorkflowStep::updateOrCreate(
            ['workflow_version_id' => $version->id, 'name' => 'Central Registry Intake & Registration'],
            [
                'step_type' => 'input',
                'role_required' => 'AGF Registry',
                'is_start_step' => true,
                'is_end_step' => false,
                'auto_return_on_rejection' => false
            ]
        );

        // Step 2: AGF Executive Direction & Minuting (Hub 1)
        $step2 = WorkflowStep::updateOrCreate(
            ['workflow_version_id' => $version->id, 'name' => 'AGF Executive Direction & Minuting'],
            [
                'step_type' => 'approval',
                'role_required' => 'super user',
                'is_start_step' => false,
                'is_end_step' => false,
                'auto_return_on_rejection' => false
            ]
        );

        // Step 3: Department Treatment & Directorate Review
        $step3 = WorkflowStep::updateOrCreate(
            ['workflow_version_id' => $version->id, 'name' => 'Department Treatment & Directorate Review'],
            [
                'step_type' => 'review',
                'role_required' => 'director',
                'is_start_step' => false,
                'is_end_step' => false,
                'auto_return_on_rejection' => true
            ]
        );

        // Step 4: Action Officer Processing & Technical Findings
        $step4 = WorkflowStep::updateOrCreate(
            ['workflow_version_id' => $version->id, 'name' => 'Action Officer Processing & Technical Findings'],
            [
                'step_type' => 'input',
                'role_required' => 'regular user',
                'is_start_step' => false,
                'is_end_step' => false,
                'auto_return_on_rejection' => false
            ]
        );

        // Step 5: AGF Final Approval & Decision (Hub 2 - Apex Approval)
        $step5 = WorkflowStep::updateOrCreate(
            ['workflow_version_id' => $version->id, 'name' => 'AGF Final Approval & Decision'],
            [
                'step_type' => 'approval',
                'role_required' => 'super user',
                'is_start_step' => false,
                'is_end_step' => false,
                'auto_return_on_rejection' => false
            ]
        );

        // Step 6: Central Registry Dispatch & Closure
        $step6 = WorkflowStep::updateOrCreate(
            ['workflow_version_id' => $version->id, 'name' => 'Central Registry Dispatch & Closure'],
            [
                'step_type' => 'approval',
                'role_required' => 'AGF Registry',
                'is_start_step' => false,
                'is_end_step' => true,
                'auto_return_on_rejection' => false
            ]
        );

        // 4. Checklists
        $this->syncChecklists($step1, [
            "Verify external correspondence and attachments",
            "Date-stamp and assign official folio/registry number"
        ]);

        $this->syncChecklists($step2, [
            "Review external correspondence and attach executive minute",
            "Select target operational Directorate"
        ]);

        $this->syncChecklists($step3, [
            "Review AGF directive and assign technical team",
            "Synthesize Directorate findings into Executive Recommendation Memo"
        ]);

        $this->syncChecklists($step4, [
            "Verify relevant financial votes, ledgers, and regulations",
            "Attach supporting computations, vouchers, or clearance documents"
        ]);

        $this->syncChecklists($step5, [
            "Review Directorate Recommendation Memo and attached dossier",
            "Authenticate with 4-Digit Approval PIN to seal decision"
        ]);

        $this->syncChecklists($step6, [
            "Verify AGF Digital Seal and Signature stamp",
            "Generate outward dispatch transmittal and release to courier/recipient",
            "Archive closed file in Central Registry"
        ]);

        // 5. Transitions
        // T1: Registry -> AGF
        WorkflowTransition::updateOrCreate(
            ['workflow_version_id' => $version->id, 'from_step_id' => $step1->id, 'to_step_id' => $step2->id],
            ['action_name' => 'Submit to AGF']
        );

        // T2: AGF -> Directorate
        WorkflowTransition::updateOrCreate(
            ['workflow_version_id' => $version->id, 'from_step_id' => $step2->id, 'to_step_id' => $step3->id],
            ['action_name' => 'Route to Department']
        );

        // T3: Directorate -> Action Officer
        WorkflowTransition::updateOrCreate(
            ['workflow_version_id' => $version->id, 'from_step_id' => $step3->id, 'to_step_id' => $step4->id],
            ['action_name' => 'Assign to Action Officer']
        );

        // T4: Action Officer -> Directorate
        WorkflowTransition::updateOrCreate(
            ['workflow_version_id' => $version->id, 'from_step_id' => $step4->id, 'to_step_id' => $step3->id],
            ['action_name' => 'Submit Findings & Technical Memo']
        );

        // T5: Directorate -> AGF Final Approval (CRUCIAL: All files return to AGF!)
        WorkflowTransition::updateOrCreate(
            ['workflow_version_id' => $version->id, 'from_step_id' => $step3->id, 'to_step_id' => $step5->id],
            ['action_name' => 'Endorse to AGF for Final Approval']
        );

        // T6: AGF Final Approval -> Registry Dispatch
        WorkflowTransition::updateOrCreate(
            ['workflow_version_id' => $version->id, 'from_step_id' => $step5->id, 'to_step_id' => $step6->id],
            ['action_name' => 'Approve & Authorize Dispatch']
        );

        // T7: AGF Return to Directorate with Query
        WorkflowTransition::updateOrCreate(
            ['workflow_version_id' => $version->id, 'from_step_id' => $step5->id, 'to_step_id' => $step3->id],
            ['action_name' => 'Return to Directorate with Query']
        );

        // 6. Step SLAs
        WorkflowSla::updateOrCreate(
            ['workflow_step_id' => $step1->id],
            ['duration_hours' => 4, 'warning_hours' => 2, 'is_working_hours_only' => true]
        );

        WorkflowSla::updateOrCreate(
            ['workflow_step_id' => $step2->id],
            ['duration_hours' => 12, 'warning_hours' => 8, 'is_working_hours_only' => true]
        );

        WorkflowSla::updateOrCreate(
            ['workflow_step_id' => $step3->id],
            ['duration_hours' => 48, 'warning_hours' => 36, 'is_working_hours_only' => true]
        );

        WorkflowSla::updateOrCreate(
            ['workflow_step_id' => $step4->id],
            ['duration_hours' => 24, 'warning_hours' => 18, 'is_working_hours_only' => true]
        );

        WorkflowSla::updateOrCreate(
            ['workflow_step_id' => $step5->id],
            ['duration_hours' => 24, 'warning_hours' => 16, 'is_working_hours_only' => true]
        );

        WorkflowSla::updateOrCreate(
            ['workflow_step_id' => $step6->id],
            ['duration_hours' => 8, 'warning_hours' => 4, 'is_working_hours_only' => true]
        );

        // 7. Workflow Rules Configuration (Workflow Settings)
        $governanceRules = [
            [
                'workflow_type' => 'executive_correspondence',
                'rule_key' => 'quick_minute_presets',
                'rule_value' => json_encode([
                    "Please Treat & Advise",
                    "For Necessary Action",
                    "Please Investigate & Report Back",
                    "Process for Payment",
                    "Urgent: Treat within 24 Hours"
                ]),
                'description' => 'Configurable 1-click civil service directive presets for the AGF Cockpit'
            ],
            [
                'workflow_type' => 'executive_correspondence',
                'rule_key' => 'approval_threshold',
                'rule_value' => '5000000',
                'description' => 'Financial approval ceiling (NGN) requiring mandatory AGF final sign-off'
            ],
            [
                'workflow_type' => 'executive_correspondence',
                'rule_key' => 'external_mail_intake_target',
                'rule_value' => 'office_of_the_agf',
                'description' => 'Target executive desk for all incoming external mail'
            ],
            [
                'workflow_type' => 'executive_correspondence',
                'rule_key' => 'agf_triage_sla_hours',
                'rule_value' => '12',
                'description' => 'Standard SLA hours for AGF executive triage of incoming mail'
            ],
            [
                'workflow_type' => 'executive_correspondence',
                'rule_key' => 'departmental_treatment_sla_hours',
                'rule_value' => '48',
                'description' => 'Standard SLA hours for departmental treatment and recommendation'
            ],
            [
                'workflow_type' => 'executive_correspondence',
                'rule_key' => 'auto_dispatch_on_agf_approval',
                'rule_value' => 'true',
                'description' => 'Automatically route docket to Registry Dispatch upon AGF PIN approval'
            ],
        ];

        foreach ($governanceRules as $gr) {
            WorkflowRule::updateOrCreate(
                ['workflow_type' => $gr['workflow_type'], 'rule_key' => $gr['rule_key']],
                ['rule_value' => $gr['rule_value'], 'description' => $gr['description']]
            );
        }

        $this->command->info('Master Routing Workflow & Governance Rules successfully seeded.');
    }

    private function syncChecklists($step, $items) {
        $checklists = [];
        foreach ($items as $item) {
            $cl = WorkflowChecklist::updateOrCreate(
                ['workflow_step_id' => $step->id, 'item_description' => $item],
                ['is_required' => true]
            );
            $checklists[] = [
                'id' => (string) $cl->id,
                'item_text' => $item,
                'is_required' => true
            ];
        }
        $step->update(['form_schema' => json_encode(['checklists' => $checklists])]);
    }
}
