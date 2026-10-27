<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Models\Department;
use App\Models\Division;
use App\Models\Unit;

class Phase10AdministrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // 2. Structural Hierarchy for Administration Department
        $adminDept = Department::firstOrCreate(
            ['title' => 'Administration'],
            ['description' => 'Administration Department', 'head' => 0]
        );

        // a. Appointment and Promotion Division (APD)
        $apdDivision = Division::firstOrCreate(
            ['title' => 'Appointment and Promotion'],
            ['department_id' => $adminDept->id, 'description' => 'Appointment and Promotion Division (APD)', 'head' => 0]
        );

        Unit::firstOrCreate(
            ['title' => 'Appointment Unit'],
            ['division_id' => $apdDivision->id, 'description' => 'Appointment Unit', 'head' => 0]
        );

        Unit::firstOrCreate(
            ['title' => 'Promotion Unit'],
            ['division_id' => $apdDivision->id, 'description' => 'Promotion Unit', 'head' => 0]
        );

        // b. Training and Staff Welfare Division (TSW)
        Division::firstOrCreate(
            ['title' => 'Training and Staff Welfare'],
            ['department_id' => $adminDept->id, 'description' => 'Training and Staff Welfare Division', 'head' => 0]
        );

        // c. Direct Reports Wrapper (Because Pool Management responds directly to Admin)
        $directReportsDivision = Division::firstOrCreate(
            ['title' => 'Administration Direct Reports'],
            ['department_id' => $adminDept->id, 'description' => 'Wrapper for Units responding directly to the Administration Dept.', 'head' => 0]
        );

        Unit::firstOrCreate(
            ['title' => 'Pool Management Section'],
            ['division_id' => $directReportsDivision->id, 'description' => 'Pool Management Section', 'head' => 0]
        );

        $this->command->info('Phase 10 Administration Department structural hierarchy seeded successfully.');
    }
}
