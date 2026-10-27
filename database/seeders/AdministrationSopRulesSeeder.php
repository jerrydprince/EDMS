<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WorkflowRule;

class AdministrationSopRulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rules = [
            [
                'workflow_type' => 'administration',
                'rule_key' => 'general_file_treatment',
                'rule_value' => json_encode(['value' => 24, 'unit' => 'working_hours']),
                'description' => 'General file treatment duration',
            ],
            [
                'workflow_type' => 'administration',
                'rule_key' => 'agf_admin_memo',
                'rule_value' => json_encode(['value' => 48, 'unit' => 'working_hours']),
                'description' => 'AGF administration memo duration',
            ],
            [
                'workflow_type' => 'administration',
                'rule_key' => 'incoming_mail_registration',
                'rule_value' => json_encode(['value' => 30, 'unit' => 'minutes']),
                'description' => 'Incoming mail registration duration',
            ],
            [
                'workflow_type' => 'administration',
                'rule_key' => 'leave_gl_06_13',
                'rule_value' => json_encode(['value' => 2, 'unit' => 'days']),
                'description' => 'Leave GL 06–13 duration',
            ],
            [
                'workflow_type' => 'administration',
                'rule_key' => 'leave_gl_14_plus',
                'rule_value' => json_encode(['value' => 5, 'unit' => 'days']),
                'description' => 'Leave GL 14+ duration',
            ],
            [
                'workflow_type' => 'administration',
                'rule_key' => 'disciplinary_query',
                'rule_value' => json_encode(['value' => 5, 'unit' => 'days']),
                'description' => 'Disciplinary query duration (from report)',
            ],
            [
                'workflow_type' => 'administration',
                'rule_key' => 'routine_pool_posting',
                'rule_value' => json_encode(['value' => 5, 'unit' => 'working_days']),
                'description' => 'Routine Pool posting duration',
            ],
            [
                'workflow_type' => 'administration',
                'rule_key' => 'urgent_pool_posting',
                'rule_value' => json_encode(['value' => 48, 'unit' => 'hours']),
                'description' => 'Urgent Pool posting duration',
            ],
            [
                'workflow_type' => 'administration',
                'rule_key' => 'training_nomination',
                'rule_value' => json_encode(['value' => 3, 'unit' => 'working_days']),
                'description' => 'Training nomination duration',
            ],
            [
                'workflow_type' => 'administration',
                'rule_key' => 'welfare_agf_approval',
                'rule_value' => json_encode(['value' => 48, 'unit' => 'hours']),
                'description' => 'Welfare requiring AGF approval duration',
            ],
        ];

        foreach ($rules as $rule) {
            WorkflowRule::updateOrCreate(
                [
                    'workflow_type' => $rule['workflow_type'],
                    'rule_key' => $rule['rule_key']
                ],
                [
                    'rule_value' => $rule['rule_value'],
                    'description' => $rule['description'],
                ]
            );
        }
    }
}
