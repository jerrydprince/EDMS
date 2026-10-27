<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use App\Models\WorkflowTransition;
use App\Models\WorkflowRule;

class Phase14Adm003Seeder extends Seeder
{
    public function run()
    {
        $adminDept = Department::where('title', 'Administration')->first();

        if (!$adminDept) {
            $this->command->error('Administration department not found.');
            return;
        }

        $this->seedAperWorkflow($adminDept);
        $this->seedPromotionBriefWorkflow($adminDept);
        $this->seedConversionWorkflow($adminDept);
        $this->seedAdvancementWorkflow($adminDept);
        $this->seedUpgradingWorkflow($adminDept);
    }

    private function seedAperWorkflow($dept)
    {
        $workflow = Workflow::firstOrCreate([
            'title' => 'ADM-003 APER Workflow',
            'department_id' => $dept->id,
        ], [
            'emoji' => '📄',
            'created_by' => 1
        ]);

        $schema = [
            [
                'name' => 'reporting_year',
                'label' => 'Reporting Year',
                'type' => 'number',
                'required' => true
            ],
            [
                'name' => 'self_assessment',
                'label' => 'Self Assessment Notes',
                'type' => 'textarea',
                'required' => true
            ],
            [
                'name' => 'evaluator_score',
                'label' => 'Evaluator Score',
                'type' => 'number',
                'required' => false
            ],
            [
                'name' => 'hod_score',
                'label' => 'HOD Score',
                'type' => 'number',
                'required' => false
            ]
        ];

        $version = $workflow->versions()->firstOrCreate([
            'version_number' => 1,
            'is_published' => true,
        ]);
        
        $version->update(['form_schema' => $schema]);

        // Define Rules
        WorkflowRule::firstOrCreate([
            'workflow_type' => 'ADM-003',
            'rule_key' => 'aper_min_score',
            'department_id' => $dept->id,
        ], [
            'rule_value' => '70',
            'description' => 'Minimum score required on APER for promotion eligibility.'
        ]);

        // Define Steps
        $step1 = WorkflowStep::firstOrCreate([
            'workflow_version_id' => $version->id,
            'name' => 'Submit APER',
            'step_type' => 'input',
            'is_start_step' => true,
            'department_id' => $dept->id,
        ]);

        $step2 = WorkflowStep::firstOrCreate([
            'workflow_version_id' => $version->id,
            'name' => 'Evaluator Review',
            'step_type' => 'review',
            'department_id' => $dept->id,
        ]);

        $step3 = WorkflowStep::firstOrCreate([
            'workflow_version_id' => $version->id,
            'name' => 'HOD Endorsement',
            'step_type' => 'approval',
            'is_end_step' => true,
            'department_id' => $dept->id,
        ]);

        WorkflowTransition::firstOrCreate([
            'workflow_version_id' => $version->id,
            'from_step_id' => $step1->id,
            'to_step_id' => $step2->id,
            'action_name' => 'Submit to Evaluator',
        ]);

        WorkflowTransition::firstOrCreate([
            'workflow_version_id' => $version->id,
            'from_step_id' => $step2->id,
            'to_step_id' => $step3->id,
            'action_name' => 'Forward to HOD',
        ]);
    }

    private function seedPromotionBriefWorkflow($dept)
    {
        $workflow = Workflow::firstOrCreate([
            'title' => 'ADM-003 Promotion Brief',
            'department_id' => $dept->id,
        ], [
            'emoji' => '📈',
            'created_by' => 1
        ]);

        $schema = [
            [
                'name' => 'current_grade',
                'label' => 'Current Grade Level',
                'type' => 'text',
                'required' => true
            ],
            [
                'name' => 'date_of_last_promotion',
                'label' => 'Date of Last Promotion',
                'type' => 'date',
                'required' => true
            ],
            [
                'name' => 'promotion_exam_score',
                'label' => 'Promotion Exam Score',
                'type' => 'number',
                'required' => false
            ]
        ];

        $version = $workflow->versions()->firstOrCreate([
            'version_number' => 1,
            'is_published' => true,
        ]);
        
        $version->update(['form_schema' => $schema]);

        WorkflowRule::firstOrCreate([
            'workflow_type' => 'ADM-003',
            'rule_key' => 'years_before_promotion',
            'department_id' => $dept->id,
        ], [
            'rule_value' => '3',
            'description' => 'Minimum years required before next promotion.'
        ]);

        // Simple steps for demonstration
        $step1 = WorkflowStep::firstOrCreate(['workflow_version_id' => $version->id, 'name' => 'Submit Brief', 'step_type' => 'input', 'is_start_step' => true, 'department_id' => $dept->id]);
        $step2 = WorkflowStep::firstOrCreate(['workflow_version_id' => $version->id, 'name' => 'HR Verification', 'step_type' => 'review', 'department_id' => $dept->id]);
        $step3 = WorkflowStep::firstOrCreate(['workflow_version_id' => $version->id, 'name' => 'Committee Review', 'step_type' => 'approval', 'is_end_step' => true, 'department_id' => $dept->id]);

        WorkflowTransition::firstOrCreate(['workflow_version_id' => $version->id, 'from_step_id' => $step1->id, 'to_step_id' => $step2->id, 'action_name' => 'Submit to HR']);
        WorkflowTransition::firstOrCreate(['workflow_version_id' => $version->id, 'from_step_id' => $step2->id, 'to_step_id' => $step3->id, 'action_name' => 'Forward to Committee']);
    }

    private function seedConversionWorkflow($dept)
    {
        $workflow = Workflow::firstOrCreate([
            'title' => 'ADM-003 Conversion Workflow',
            'department_id' => $dept->id,
        ], [
            'emoji' => '🔄',
            'created_by' => 1
        ]);

        $schema = [
            [
                'name' => 'current_cadre',
                'label' => 'Current Cadre',
                'type' => 'text',
                'required' => true
            ],
            [
                'name' => 'target_cadre',
                'label' => 'Target Cadre',
                'type' => 'text',
                'required' => true
            ],
            [
                'name' => 'qualifications',
                'label' => 'Qualifications Acquired',
                'type' => 'textarea',
                'required' => true
            ]
        ];

        $version = $workflow->versions()->firstOrCreate([
            'version_number' => 1,
            'is_published' => true,
        ]);
        
        $version->update(['form_schema' => $schema]);

        $step1 = WorkflowStep::firstOrCreate(['workflow_version_id' => $version->id, 'name' => 'Apply for Conversion', 'step_type' => 'input', 'is_start_step' => true, 'department_id' => $dept->id]);
        $step2 = WorkflowStep::firstOrCreate(['workflow_version_id' => $version->id, 'name' => 'HR Assessment', 'step_type' => 'review', 'is_end_step' => true, 'department_id' => $dept->id]);
        WorkflowTransition::firstOrCreate(['workflow_version_id' => $version->id, 'from_step_id' => $step1->id, 'to_step_id' => $step2->id, 'action_name' => 'Submit Application']);
    }

    private function seedAdvancementWorkflow($dept)
    {
        $workflow = Workflow::firstOrCreate([
            'title' => 'ADM-003 Advancement Workflow',
            'department_id' => $dept->id,
        ], [
            'emoji' => '⬆️',
            'created_by' => 1
        ]);

        $schema = [
            [
                'name' => 'current_step',
                'label' => 'Current Step',
                'type' => 'text',
                'required' => true
            ],
            [
                'name' => 'target_step',
                'label' => 'Target Step',
                'type' => 'text',
                'required' => true
            ]
        ];

        $version = $workflow->versions()->firstOrCreate([
            'version_number' => 1,
            'is_published' => true,
        ]);
        
        $version->update(['form_schema' => $schema]);

        $step1 = WorkflowStep::firstOrCreate(['workflow_version_id' => $version->id, 'name' => 'Apply for Advancement', 'step_type' => 'input', 'is_start_step' => true, 'department_id' => $dept->id]);
        $step2 = WorkflowStep::firstOrCreate(['workflow_version_id' => $version->id, 'name' => 'Approval', 'step_type' => 'approval', 'is_end_step' => true, 'department_id' => $dept->id]);
        WorkflowTransition::firstOrCreate(['workflow_version_id' => $version->id, 'from_step_id' => $step1->id, 'to_step_id' => $step2->id, 'action_name' => 'Submit']);
    }

    private function seedUpgradingWorkflow($dept)
    {
        $workflow = Workflow::firstOrCreate([
            'title' => 'ADM-003 Upgrading Workflow',
            'department_id' => $dept->id,
        ], [
            'emoji' => '⭐',
            'created_by' => 1
        ]);

        $schema = [
            [
                'name' => 'certificate_name',
                'label' => 'New Certificate / Qualification',
                'type' => 'text',
                'required' => true
            ],
            [
                'name' => 'accrediting_body',
                'label' => 'Accrediting Body',
                'type' => 'text',
                'required' => true
            ]
        ];

        $version = $workflow->versions()->firstOrCreate([
            'version_number' => 1,
            'is_published' => true,
        ]);
        
        $version->update(['form_schema' => $schema]);

        $step1 = WorkflowStep::firstOrCreate(['workflow_version_id' => $version->id, 'name' => 'Apply for Upgrading', 'step_type' => 'input', 'is_start_step' => true, 'department_id' => $dept->id]);
        $step2 = WorkflowStep::firstOrCreate(['workflow_version_id' => $version->id, 'name' => 'Verification', 'step_type' => 'approval', 'is_end_step' => true, 'department_id' => $dept->id]);
        WorkflowTransition::firstOrCreate(['workflow_version_id' => $version->id, 'from_step_id' => $step1->id, 'to_step_id' => $step2->id, 'action_name' => 'Submit']);
    }
}
