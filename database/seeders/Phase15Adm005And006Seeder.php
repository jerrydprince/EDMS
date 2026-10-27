<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Workflow;
use App\Models\WorkflowVersion;
use App\Models\WorkflowStep;
use App\Models\WorkflowSla;

class Phase15Adm005And006Seeder extends Seeder
{
    public function run(): void
    {
        $trainingWorkflows = [
            [
                'title' => 'ADM-005 Training Needs Analysis (TNA)',
                'description' => 'Analyze department training needs.',
                'schema' => [
                    ['name' => 'department', 'label' => 'Department', 'type' => 'text', 'required' => true],
                    ['name' => 'skill_gap', 'label' => 'Skill Gap Identified', 'type' => 'textarea', 'required' => true],
                    ['name' => 'required_competency', 'label' => 'Required Competency', 'type' => 'text', 'required' => true]
                ]
            ],
            [
                'title' => 'ADM-005 Training Plan',
                'description' => 'Propose and approve training plans.',
                'schema' => [
                    ['name' => 'proposed_course', 'label' => 'Proposed Course', 'type' => 'text', 'required' => true],
                    ['name' => 'budget', 'label' => 'Estimated Budget', 'type' => 'number', 'required' => true],
                    ['name' => 'target_roles', 'label' => 'Target Roles', 'type' => 'text', 'required' => true]
                ]
            ],
            [
                'title' => 'ADM-005 Training Nomination',
                'description' => 'Nominate staff for training.',
                'schema' => [
                    ['name' => 'nominee_details', 'label' => 'Nominee Details', 'type' => 'text', 'required' => true],
                    ['name' => 'course', 'label' => 'Course Name', 'type' => 'text', 'required' => true],
                    ['name' => 'justification', 'label' => 'Justification', 'type' => 'textarea', 'required' => true]
                ]
            ],
            [
                'title' => 'ADM-005 Training Record',
                'description' => 'Submit post-training records and feedback.',
                'schema' => [
                    ['name' => 'certificate_upload', 'label' => 'Certificate URL / Reference', 'type' => 'text', 'required' => true],
                    ['name' => 'feedback_score', 'label' => 'Feedback Score (1-10)', 'type' => 'number', 'required' => true]
                ]
            ],
            [
                'title' => 'ADM-005 Training Quarterly Report',
                'description' => 'Quarterly training report submission.',
                'schema' => [
                    ['name' => 'trainee_stats', 'label' => 'Number of Trainees', 'type' => 'number', 'required' => true],
                    ['name' => 'budget_utilization', 'label' => 'Budget Utilized', 'type' => 'number', 'required' => true]
                ]
            ]
        ];

        $welfareWorkflows = [
            [
                'title' => 'ADM-006 NHIS Registration/Update',
                'description' => 'Register or update NHIS details.',
                'schema' => [
                    ['name' => 'hmo_preference', 'label' => 'HMO Preference', 'type' => 'text', 'required' => true],
                    ['name' => 'dependents', 'label' => 'Number of Dependents', 'type' => 'number', 'required' => true]
                ]
            ],
            [
                'title' => 'ADM-006 Pension Processing',
                'description' => 'Process pension details.',
                'schema' => [
                    ['name' => 'pfa_details', 'label' => 'PFA Name', 'type' => 'text', 'required' => true],
                    ['name' => 'rsa_pin', 'label' => 'RSA PIN', 'type' => 'text', 'required' => true]
                ]
            ],
            [
                'title' => 'ADM-006 Housing Scheme',
                'description' => 'Apply for staff housing scheme.',
                'schema' => [
                    ['name' => 'location_preference', 'label' => 'Location Preference', 'type' => 'text', 'required' => true],
                    ['name' => 'grade_level', 'label' => 'Grade Level', 'type' => 'text', 'required' => true]
                ]
            ],
            [
                'title' => 'ADM-006 Burial Assistance',
                'description' => 'Apply for burial assistance.',
                'schema' => [
                    ['name' => 'next_of_kin', 'label' => 'Next of Kin', 'type' => 'text', 'required' => true],
                    ['name' => 'death_certificate', 'label' => 'Death Certificate Ref', 'type' => 'text', 'required' => true]
                ]
            ],
            [
                'title' => 'ADM-006 Staff Loans',
                'description' => 'Apply for staff loans.',
                'schema' => [
                    ['name' => 'loan_type', 'label' => 'Loan Type', 'type' => 'select', 'options' => ['Vehicle', 'Personal', 'Mortgage'], 'required' => true],
                    ['name' => 'amount_requested', 'label' => 'Amount Requested', 'type' => 'number', 'required' => true],
                    ['name' => 'repayment_period', 'label' => 'Repayment Period (Months)', 'type' => 'number', 'required' => true]
                ]
            ],
            [
                'title' => 'ADM-006 Awards/Recognition',
                'description' => 'Nominate staff for awards.',
                'schema' => [
                    ['name' => 'nominee', 'label' => 'Nominee Name', 'type' => 'text', 'required' => true],
                    ['name' => 'category', 'label' => 'Award Category', 'type' => 'text', 'required' => true],
                    ['name' => 'justification', 'label' => 'Justification', 'type' => 'textarea', 'required' => true]
                ]
            ]
        ];

        $allWorkflows = array_merge($trainingWorkflows, $welfareWorkflows);

        foreach ($allWorkflows as $wfData) {
            $workflow = Workflow::firstOrCreate(
                ['title' => $wfData['title']],
                ['status' => 'published', 'created_by' => 1]
            );

            // Create a version and associate the schema
            $version = WorkflowVersion::firstOrCreate(
                ['workflow_id' => $workflow->id, 'version_number' => 1],
                ['is_published' => true, 'form_schema' => $wfData['schema']]
            );

            // Create steps
            $step1 = WorkflowStep::firstOrCreate([
                'workflow_version_id' => $version->id,
                'name' => 'HR Verification',
                'step_type' => 'approval',
                'role_required' => 'admin',
                'is_start_step' => true,
                'is_end_step' => false
            ]);

            $step2 = WorkflowStep::firstOrCreate([
                'workflow_version_id' => $version->id,
                'name' => 'Director Approval',
                'step_type' => 'approval',
                'role_required' => 'super admin',
                'is_start_step' => false,
                'is_end_step' => true
            ]);

            // Add SLAs
            WorkflowSla::firstOrCreate(
                ['workflow_step_id' => $step1->id],
                [
                    'duration_hours' => 48,
                    'warning_hours' => 24,
                    'escalation_role' => 'super admin',
                    'escalation_action' => 'notify'
                ]
            );

            WorkflowSla::firstOrCreate(
                ['workflow_step_id' => $step2->id],
                [
                    'duration_hours' => 72,
                    'warning_hours' => 48,
                    'escalation_role' => 'super admin',
                    'escalation_action' => 'reassign'
                ]
            );
        }
    }
}
