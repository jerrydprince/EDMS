<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Designation;

class DesignationSeeder extends Seeder
{
    public function run(): void
    {
        $matrix = [
            'Accounting' => [
                ['grade_level' => 8, 'title' => 'Accountant II'],
                ['grade_level' => 9, 'title' => 'Accountant I'],
                ['grade_level' => 10, 'title' => 'Senior Accountant'],
                ['grade_level' => 12, 'title' => 'Principal Accountant'],
                ['grade_level' => 13, 'title' => 'Assistant Chief Accountant'],
                ['grade_level' => 14, 'title' => 'Chief Accountant'],
                ['grade_level' => 15, 'title' => 'Assistant Director'],
                ['grade_level' => 16, 'title' => 'Deputy Director'],
                ['grade_level' => 17, 'title' => 'Director'],
            ],
            'Admin' => [
                ['grade_level' => 8, 'title' => 'Administrative Officer II'],
                ['grade_level' => 9, 'title' => 'Administrative Officer I'],
                ['grade_level' => 10, 'title' => 'Senior Administrative Officer'],
                ['grade_level' => 12, 'title' => 'Principal Administrative Officer'],
                ['grade_level' => 13, 'title' => 'Assistant Chief Administrative Officer'],
                ['grade_level' => 14, 'title' => 'Assistant Director'],
                ['grade_level' => 15, 'title' => 'Deputy Director'],
                ['grade_level' => 16, 'title' => 'Director'],
            ],
            'Planning' => [
                ['grade_level' => 8, 'title' => 'Planning Officer II / Statistician II'],
                ['grade_level' => 9, 'title' => 'Planning Officer I / Statistician I'],
                ['grade_level' => 10, 'title' => 'Senior Planning Officer / Senior Statistician'],
                ['grade_level' => 12, 'title' => 'Principal Planning Officer / Principal Statistician'],
                ['grade_level' => 13, 'title' => 'Assistant Chief Planning Officer'],
                ['grade_level' => 14, 'title' => 'Assistant Director'],
                ['grade_level' => 15, 'title' => 'Deputy Director'],
                ['grade_level' => 16, 'title' => 'Director'],
            ],
            'Legal' => [
                ['grade_level' => 10, 'title' => 'Legal Officer II / State Counsel'],
                ['grade_level' => 12, 'title' => 'Legal Officer I / Senior State Counsel'],
                ['grade_level' => 13, 'title' => 'Principal Legal Officer / Principal State Counsel'],
                ['grade_level' => 14, 'title' => 'Assistant Chief Legal Officer / Assistant Director'],
                ['grade_level' => 15, 'title' => 'Deputy Director'],
                ['grade_level' => 16, 'title' => 'Director'],
            ],
            'Information and Communications Technology' => [
                ['grade_level' => 8, 'title' => 'Program Analyst II / ICT Officer II'],
                ['grade_level' => 9, 'title' => 'Program Analyst I / ICT Officer I'],
                ['grade_level' => 10, 'title' => 'Senior Program Analyst / Senior ICT Officer'],
                ['grade_level' => 12, 'title' => 'Principal Program Analyst / Principal ICT Officer'],
                ['grade_level' => 13, 'title' => 'Assistant Chief Program Analyst / Assistant Chief ICT Officer'],
                ['grade_level' => 14, 'title' => 'Assistant Director'],
                ['grade_level' => 15, 'title' => 'Deputy Director'],
                ['grade_level' => 16, 'title' => 'Director'],
            ],
            'Press' => [
                ['grade_level' => 8, 'title' => 'Information Officer II'],
                ['grade_level' => 9, 'title' => 'Information Officer I'],
                ['grade_level' => 10, 'title' => 'Senior Information Officer'],
                ['grade_level' => 12, 'title' => 'Principal Information Officer'],
                ['grade_level' => 13, 'title' => 'Assistant Chief Information Officer'],
                ['grade_level' => 14, 'title' => 'Assistant Director'],
                ['grade_level' => 15, 'title' => 'Deputy Director'],
                ['grade_level' => 16, 'title' => 'Director'],
            ],
            'General Services' => [
                ['grade_level' => 8, 'title' => 'Officer II (General Services / Estate)'],
                ['grade_level' => 9, 'title' => 'Officer I (General Services)'],
                ['grade_level' => 10, 'title' => 'Senior Officer (General Services)'],
                ['grade_level' => 12, 'title' => 'Principal Officer (General Services)'],
                ['grade_level' => 13, 'title' => 'Assistant Chief Officer (General Services)'],
                ['grade_level' => 14, 'title' => 'Assistant Director (General Services)'],
                ['grade_level' => 15, 'title' => 'Deputy Director (General Services)'],
                ['grade_level' => 16, 'title' => 'Director (General Services)'],
            ],
            'Library' => [
                ['grade_level' => 8, 'title' => 'Librarian II'],
                ['grade_level' => 9, 'title' => 'Librarian I'],
                ['grade_level' => 10, 'title' => 'Senior Librarian'],
                ['grade_level' => 12, 'title' => 'Principal Librarian'],
                ['grade_level' => 13, 'title' => 'Assistant Chief Librarian'],
                ['grade_level' => 14, 'title' => 'Assistant Director (Library Services)'],
                ['grade_level' => 15, 'title' => 'Deputy Director (Library Services)'],
                ['grade_level' => 16, 'title' => 'Director (Library Services)'],
            ],
            'Special Duties' => [
                ['grade_level' => 8, 'title' => 'Special Duties Officer II'],
                ['grade_level' => 9, 'title' => 'Special Duties Officer I'],
                ['grade_level' => 10, 'title' => 'Senior Special Duties Officer'],
                ['grade_level' => 12, 'title' => 'Principal Special Duties Officer'],
                ['grade_level' => 13, 'title' => 'Assistant Chief Special Duties Officer'],
                ['grade_level' => 14, 'title' => 'Assistant Director (Special Duties)'],
                ['grade_level' => 15, 'title' => 'Deputy Director (Special Duties)'],
                ['grade_level' => 16, 'title' => 'Director (Special Duties)'],
            ],
            'Procurement' => [
                ['grade_level' => 8, 'title' => 'Procurement Officer II'],
                ['grade_level' => 9, 'title' => 'Procurement Officer I'],
                ['grade_level' => 10, 'title' => 'Senior Procurement Officer'],
                ['grade_level' => 12, 'title' => 'Principal Procurement Officer'],
                ['grade_level' => 13, 'title' => 'Assistant Chief Procurement Officer'],
                ['grade_level' => 14, 'title' => 'Assistant Director (Procurement)'],
                ['grade_level' => 15, 'title' => 'Deputy Director (Procurement)'],
                ['grade_level' => 16, 'title' => 'Director (Procurement)'],
            ],
        ];

        foreach ($matrix as $deptName => $designations) {
            $departments = Department::where('title', 'LIKE', '%' . $deptName . '%')->get();
            
            // Special handling for Accounting related departments since the user mentioned they have multiple
            if ($deptName === 'Accounting') {
                $departments = Department::whereIn('title', [
                    'Funds', 
                    'Finance and Accounts', 
                    'Revenue and Investment'
                ])->get();
            }

            foreach ($departments as $department) {
                foreach ($designations as $desig) {
                    Designation::updateOrCreate(
                        [
                            'department_id' => $department->id,
                            'grade_level' => $desig['grade_level']
                        ],
                        [
                            'title' => $desig['title']
                        ]
                    );
                }
            }
        }
    }
}
