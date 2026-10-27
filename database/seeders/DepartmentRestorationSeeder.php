<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentRestorationSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            'Funds Department',
            'Finance and Accounts Department',
            'Revenue and Investment Department',
            'Procurement Department',
            'Information and Communications Technology Department',
            'Admin Department',
        ];

        foreach ($departments as $dept) {
            Department::updateOrCreate(
                ['title' => $dept],
                ['description' => $dept, 'head' => 0]
            );
        }
    }
}
