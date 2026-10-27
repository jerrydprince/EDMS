<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Designation;

class FunctionalDesignationsSeeder extends Seeder
{
    public function run(): void
    {
        $functionalDes = [
            [
                'title' => 'Registry Officer',
                'cadre' => 'Executive Officer / Registry',
                'grade_level' => 9,
                'hierarchy_level' => 'global',
                'is_registry' => true,
                'is_head_of_office' => false,
                'description' => 'Manages inward/outward mail indexing, file jacket tracking, and correspondence movement'
            ],
            [
                'title' => 'Central Registry Officer',
                'cadre' => 'Executive Officer / Central Registry',
                'grade_level' => 10,
                'hierarchy_level' => 'global',
                'is_registry' => true,
                'is_head_of_office' => false,
                'description' => 'Manages AGF Central Ministerial Registry, external ministerial mail intake, and executive dispatch'
            ],
            [
                'title' => 'Director / Head of Department',
                'cadre' => 'Administrative / Professional',
                'grade_level' => 17,
                'hierarchy_level' => 'department',
                'is_registry' => false,
                'is_head_of_office' => true,
                'description' => 'Executive head of department with final approving and minuting authority'
            ],
            [
                'title' => 'Deputy Director / Head of Division',
                'cadre' => 'Administrative / Professional',
                'grade_level' => 16,
                'hierarchy_level' => 'division',
                'is_registry' => false,
                'is_head_of_office' => true,
                'description' => 'Division head overseeing operational units and division-level review'
            ],
            [
                'title' => 'Assistant Director / Head of Unit',
                'cadre' => 'Administrative / Professional',
                'grade_level' => 15,
                'hierarchy_level' => 'unit',
                'is_registry' => false,
                'is_head_of_office' => true,
                'description' => 'Unit head supervising section desk officers and specialized operations'
            ],
            [
                'title' => 'Checking Officer',
                'cadre' => 'Accountant / Checking',
                'grade_level' => 10,
                'hierarchy_level' => 'section',
                'is_registry' => false,
                'is_head_of_office' => false,
                'description' => 'Verifies arithmetic accuracy, vote book commitment, and affixes statutory Digital Checking Stamp'
            ],
            [
                'title' => 'Pre-Payment Internal Auditor',
                'cadre' => 'Internal Auditor',
                'grade_level' => 12,
                'hierarchy_level' => 'section',
                'is_registry' => false,
                'is_head_of_office' => false,
                'description' => 'Performs statutory pre-payment audit inspection, issues audit queries, and affixes Audit Clearance Seal'
            ],
            [
                'title' => 'Cashier / Cash Office Officer',
                'cadre' => 'Accountant / Cashier',
                'grade_level' => 9,
                'hierarchy_level' => 'section',
                'is_registry' => false,
                'is_head_of_office' => false,
                'description' => 'Executes CBN TSA payment mandate, manages bank disbursements, and maintains Cash Book'
            ],
            [
                'title' => 'Vote Book Officer',
                'cadre' => 'Accountant / Budget',
                'grade_level' => 10,
                'hierarchy_level' => 'section',
                'is_registry' => false,
                'is_head_of_office' => false,
                'description' => 'Monitors budget sub-heads, enters approved allocations, commits liabilities, and liquidates votes'
            ],
            [
                'title' => 'Final Accounts Officer',
                'cadre' => 'Accountant / Financial Reporting',
                'grade_level' => 12,
                'hierarchy_level' => 'unit',
                'is_registry' => false,
                'is_head_of_office' => false,
                'description' => 'Prepares dual-entry balanced IPSAS journal vouchers and posts to General Ledger'
            ],
            [
                'title' => 'Advances Ledger Officer',
                'cadre' => 'Accountant / Staff Advances',
                'grade_level' => 10,
                'hierarchy_level' => 'section',
                'is_registry' => false,
                'is_head_of_office' => false,
                'description' => 'Manages advances subsidiary ledger, verifies retirements, and monitors FR 1420 compliance'
            ],
            [
                'title' => 'Desk Officer / Processing Officer',
                'cadre' => 'Executive / Administrative',
                'grade_level' => 8,
                'hierarchy_level' => 'global',
                'is_registry' => false,
                'is_head_of_office' => false,
                'description' => 'General desk processing officer handling departmental correspondence and workflow tasks'
            ],
            [
                'title' => 'Personal Assistant / Confidential Secretary',
                'cadre' => 'Secretarial / Administration',
                'grade_level' => 9,
                'hierarchy_level' => 'global',
                'is_registry' => false,
                'is_head_of_office' => false,
                'description' => 'Confidential secretary to Director or Division Head managing executive schedule and files'
            ],
            [
                'title' => 'Store / Procurement Officer',
                'cadre' => 'Procurement / Store Officer',
                'grade_level' => 9,
                'hierarchy_level' => 'global',
                'is_registry' => false,
                'is_head_of_office' => false,
                'description' => 'Manages store receipt vouchers, procurement documentation, and inventory records'
            ],
        ];

        $faDept = \App\Models\Department::where('title', 'Finance and Accounts')->first();
        $faId = $faDept ? $faDept->id : 3;

        foreach ($functionalDes as $item) {
            $isFaSpecific = in_array($item['title'], [
                'Checking Officer',
                'Pre-Payment Internal Auditor',
                'Cashier / Cash Office Officer',
                'Vote Book Officer',
                'Final Accounts Officer',
                'Advances Ledger Officer'
            ]);

            $deptId = $isFaSpecific ? $faId : null;
            $title = $isFaSpecific ? $item['title'] . ' (Finance and Accounts)' : $item['title'];

            Designation::firstOrCreate(
                ['title' => $title, 'department_id' => $deptId],
                array_merge($item, [
                    'title' => $title,
                    'department_id' => $deptId,
                    'is_active' => true
                ])
            );
        }
    }
}
