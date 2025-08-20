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
                'name' => 'directors', 
                'guard_name' => 'web', 
                'description' => 'Manages departmental functions, shares files/documents with other directors, accesses department files',
                'created_at'=>\Carbon\Carbon::now(), 
                'updated_at'=>\Carbon\Carbon::now()
            ], 
            [
                'name' => 'division heads', 
                'guard_name' => 'web', 
                'description'=> "Manages a specific division's activities, accesses division files and shares documents with the director and other division heads",
                'created_at'=>\Carbon\Carbon::now(), 
                'updated_at'=>\Carbon\Carbon::now()
            ], 
            [
                'name' => 'unit heads', 
                'guard_name' => 'web', 
                'description' => 'Manages activities of a specific unit within a division, accesses unit files and shares documents with the division head and other unit heads',
                'created_at'=>\Carbon\Carbon::now(), 
                'updated_at'=>\Carbon\Carbon::now()
            ],  
            [
                'name' => 'record manager', 
                'guard_name' => 'web', 
                'description' => "Specialized in records management, ensuring compliance with government regulations",
                'created_at'=>\Carbon\Carbon::now(), 
                'updated_at'=>\Carbon\Carbon::now()
            ], 
            [
                'name' => 'AGF record manager', 
                'guard_name' => 'web', 
                'description' => "Specialized in records management, ensuring compliance with government regulations",
                'created_at'=>\Carbon\Carbon::now(), 
                'updated_at'=>\Carbon\Carbon::now()
            ], 
            [
                'name' => 'super users', 
                'guard_name' => 'web', 
                'description' => 'Manages cross-department activities, oversees all files/documents, delegates tasks, tracks file activities',
                'created_at'=>\Carbon\Carbon::now(), 
                'updated_at'=>\Carbon\Carbon::now()
            ], 
            [
                'name' => 'regular users', 
                'guard_name' => 'web', 
                'description' => 'Manages cross-department activities, oversees all files/documents, delegates tasks, tracks file activities',
                'created_at'=>\Carbon\Carbon::now(), 
                'updated_at'=>\Carbon\Carbon::now()
            ], 
            [
                'name' => 'data entry', 
                'guard_name' => 'web', 
                'description' => 'Limited access for entering data into the system, including tasks like scanning and indexing documents',
                'created_at'=>\Carbon\Carbon::now(), 
                'updated_at'=>\Carbon\Carbon::now()
            ],
        ];
        Role::insert($roles);
    }
}
