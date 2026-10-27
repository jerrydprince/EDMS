<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Department;
use App\Models\Division;
use App\Models\Unit;
use App\Models\Section;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // First, clean up the top-level 10 departments we accidentally created last time
        $wrongDepts = [
            'Office of the Chief of Staff (GL17)',
            'Office of the Special Adviser/Technical Adviser',
            'Executive Secretariat Department',
            'Strategic Management & Coordination Department',
            'Executive Communications Department',
            'Internal Control & Executive Compliance Department',
            'Legal & Executive Advisory Department',
            'Executive Projects & Special Duties Department',
            'Executive Monitoring & Performance Department',
            'Executive Protocol & Stakeholder Relations Department'
        ];

        DB::table('departments')->whereIn('title', $wrongDepts)->update(['deleted_at' => now(), 'deleted_by' => 0]);

        // Restore AGF properly
        $agfTitle = 'OFFICE OF THE ACCOUNTANT-GENERAL OF THE FEDERATION (EXECUTIVE OFFICE)';
        DB::table('departments')->where('title', 'like', '%Accountant-General of the Federation%')->update(['deleted_at' => null]);
        
        $data = [
            $agfTitle => [
                'Office of the Chief of Staff (GL17)' => [
                    'Executive Coordination Division' => [
                        'Daily Schedule Unit',
                        'Executive Assignments Unit',
                        'Decision Tracking Unit',
                        'Executive Correspondence Unit'
                    ],
                    'Executive Support Division' => [
                        'Personal Assistants Unit',
                        'Administrative Support Unit',
                        'Executive Records Unit',
                        'Office Operations Unit'
                    ],
                    'Executive Follow-up Division' => [
                        'Action Tracking Unit',
                        'Executive Directives Unit',
                        'Escalation Unit',
                        'Executive Dashboard Unit'
                    ]
                ],
                'Office of the Special Adviser/Technical Adviser' => [
                    'Treasury Policy Division' => [
                        'Treasury Reform Unit',
                        'Financial Policy Unit',
                        'Strategic Advisory Unit'
                    ],
                    'ICT & Digital Transformation Division' => [
                        'Enterprise Architecture Unit',
                        'Digital Treasury Unit',
                        'AI & Innovation Unit',
                        'Emerging Technology Unit'
                    ],
                    'Economic Advisory Division' => [
                        'Fiscal Analysis Unit',
                        'Macroeconomic Unit',
                        'Investment Analysis Unit',
                        'Executive Research Unit'
                    ]
                ],
                'Executive Secretariat Department' => [
                    'Council Affairs Division' => [],
                    'Executive Memo Division' => [],
                    'Executive Correspondence Division' => [],
                    'Meeting Management Division' => [],
                    'Executive Records Division' => []
                ],
                'Strategic Management & Coordination Department' => [
                    'Strategic Planning Division' => [],
                    'Corporate Performance Division' => [],
                    'Department Coordination Division' => [],
                    'Treasury Initiatives Division' => [],
                    'Enterprise Risk Division' => []
                ],
                'Executive Communications Department' => [
                    'Internal Communication Division' => [],
                    'External Communication Division' => [],
                    'Speech Writing Division' => [],
                    'Media Coordination Division' => [],
                    'Digital Communications Division' => []
                ],
                'Internal Control & Executive Compliance Department' => [
                    'Executive Audit Division' => [],
                    'Compliance Division' => [],
                    'Risk Management Division' => [],
                    'Quality Assurance Division' => [],
                    'Investigation Division' => []
                ],
                'Legal & Executive Advisory Department' => [
                    'Legal Advisory Division' => [],
                    'Contracts Division' => [],
                    'Litigation Division' => [],
                    'Regulatory Affairs Division' => [],
                    'Executive Opinions Division' => []
                ],
                'Executive Projects & Special Duties Department' => [
                    'Presidential Directives Division' => [],
                    'Reform Projects Division' => [],
                    'Special Assignments Division' => [],
                    'National Projects Division' => [],
                    'Innovation Projects Division' => []
                ],
                'Executive Monitoring & Performance Department' => [
                    'Performance Monitoring Division' => [],
                    'Department KPI Division' => [],
                    'Executive Dashboard Division' => [],
                    'Treasury Analytics Division' => [],
                    'Performance Evaluation Division' => []
                ],
                'Executive Protocol & Stakeholder Relations Department' => [
                    'Protocol Division' => [],
                    'International Relations Division' => [],
                    'Stakeholder Management Division' => [],
                    'Visitor Management Division' => [],
                    'Event Coordination Division' => []
                ]
            ]
        ];

        foreach ($data as $deptTitle => $divisions) {
            $dept = Department::firstOrCreate([
                'title' => $deptTitle
            ], [
                'description' => $deptTitle
            ]);

            foreach ($divisions as $divTitle => $units) {
                $div = Division::firstOrCreate([
                    'title' => $divTitle,
                    'department_id' => $dept->id
                ], [
                    'description' => $divTitle,
                    'head' => 0
                ]);

                if (is_array($units)) {
                    foreach ($units as $unitTitle => $sections) {
                        if (is_int($unitTitle)) {
                            // It's a flat array of units (from when there were no sections)
                            Unit::firstOrCreate([
                                'title' => $sections,
                                'division_id' => $div->id
                            ], [
                                'description' => $sections,
                                'head' => 0
                            ]);
                        } else {
                            $unit = Unit::firstOrCreate([
                                'title' => $unitTitle,
                                'division_id' => $div->id
                            ], [
                                'description' => $unitTitle,
                                'head' => 0
                            ]);

                            if (is_array($sections)) {
                                foreach ($sections as $sectionTitle) {
                                    Section::firstOrCreate([
                                        'title' => $sectionTitle,
                                        'unit_id' => $unit->id
                                    ], [
                                        'description' => $sectionTitle,
                                        'head' => 0
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
        }
        
        $this->command->info("AGF Branches restored successfully!");
    }
}
