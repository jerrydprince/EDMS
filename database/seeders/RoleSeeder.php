<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'super admin', 
                'guard_name' => 'web',
                'description' => 'This access level holds unparalleled authority, overseeing the entire system without restrictions',
                'created_at'=>\Carbon\Carbon::now(), 
                'updated_at'=>\Carbon\Carbon::now(),
            ], 
            [
                'name' => 'admin', 
                'guard_name' => 'web',
                'description' => 'Focuses on document management. Configures workflows, archiving policies, and document-specific settings', 
                'created_at'=>\Carbon\Carbon::now(), 
                'updated_at'=>\Carbon\Carbon::now()
            ], 
            [
                'name' => 'director', 
                'guard_name' => 'web', 
                'description' => 'Manages departmental functions, shares files/documents with other directors, accesses department files',
                'created_at'=>\Carbon\Carbon::now(), 
                'updated_at'=>\Carbon\Carbon::now()
            ], 
            [
                'name' => 'deputy director', 
                'guard_name' => 'web', 
                'description'=> "Manages a specific division's activities, accesses division files and shares documents with the director and other deputy directors",
                'created_at'=>\Carbon\Carbon::now(), 
                'updated_at'=>\Carbon\Carbon::now()
            ], 
            [
                'name' => 'assistant director', 
                'guard_name' => 'web', 
                'description' => 'Manages activities of a specific unit within a division, accesses unit files and shares documents with the deputy director and other assistant directors',
                'created_at'=>\Carbon\Carbon::now(), 
                'updated_at'=>\Carbon\Carbon::now()
            ],  
            [
                'name' => 'record manager', 
                'guard_name' => 'web', 
                'description' => "Specialized in records management, ensuring compliance with government regulations, with full access to entering data into the system, including tasks like scanning and indexing documents",
                'created_at'=>\Carbon\Carbon::now(), 
                'updated_at'=>\Carbon\Carbon::now()
            ], 
            [
                'name' => 'registry', 
                'guard_name' => 'web', 
                'description' => "Manages all incoming and outgoing documents, indexing, routing, and tracking based on department context",
                'created_at'=>\Carbon\Carbon::now(), 
                'updated_at'=>\Carbon\Carbon::now()
            ], 
            [
                'name' => 'super user', 
                'guard_name' => 'web', 
                'description' => 'Manages cross-department activities, oversees all files/documents, delegates tasks, tracks file activities',
                'created_at'=>\Carbon\Carbon::now(), 
                'updated_at'=>\Carbon\Carbon::now()
            ], 
            [
                'name' => 'regular user', 
                'guard_name' => 'web', 
                'description' => 'Manages cross-department activities, oversees all files/documents, delegates tasks, tracks file activities',
                'created_at'=>\Carbon\Carbon::now(), 
                'updated_at'=>\Carbon\Carbon::now()
            ], 

            [
                'name' => 'Accountant-General',
                'guard_name' => 'web',
                'description' => 'Full enterprise control over all modules, organizations, approvals, dashboards, financial authorizations, policy management, and executive analytics',
                'created_at'=>\Carbon\Carbon::now(),
                'updated_at'=>\Carbon\Carbon::now()
            ],
            [
                'name' => 'Chief of Staff',
                'guard_name' => 'web',
                'description' => 'Executive coordination, assignment management, workflow monitoring, cross-department tracking, and escalation',
                'created_at'=>\Carbon\Carbon::now(),
                'updated_at'=>\Carbon\Carbon::now()
            ],
            [
                'name' => 'Special Adviser / Technical Adviser',
                'guard_name' => 'web',
                'description' => 'Policy review, strategic analysis, advisory dashboards, reform tracking, and document review',
                'created_at'=>\Carbon\Carbon::now(),
                'updated_at'=>\Carbon\Carbon::now()
            ],

            [
                'name' => 'secretary',
                'guard_name' => 'web',
                'description' => 'Executive correspondence, meeting management, minute preparation, document routing, and records management for Agf office, Directors, Deputy Directors and Assistant Directors',
                'created_at'=>\Carbon\Carbon::now(),
                'updated_at'=>\Carbon\Carbon::now()
            ]
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['name' => $roleData['name'], 'guard_name' => $roleData['guard_name']],
                $roleData
            );
        }
    }
}
