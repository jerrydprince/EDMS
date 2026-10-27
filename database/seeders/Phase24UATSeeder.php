<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Department;
use App\Models\Division;
use App\Models\Unit;
use Spatie\Permission\Models\Role;

class Phase24UATSeeder extends Seeder
{
    public function run()
    {
        // We will seed the specific UAT accounts based on Phase10 structure
        $adminDept = Department::where('title', 'Administration')->first();
        $apdDivision = Division::where('title', 'Appointment and Promotion')->where('department_id', $adminDept->id)->first();
        $tswDivision = Division::where('title', 'Training and Staff Welfare')->where('department_id', $adminDept->id)->first();
        $directReportsDivision = Division::where('title', 'Administration Direct Reports')->where('department_id', $adminDept->id)->first();
        
        $apptUnit = Unit::where('title', 'Appointment Unit')->where('division_id', $apdDivision->id)->first();
        $promoUnit = Unit::where('title', 'Promotion Unit')->where('division_id', $apdDivision->id)->first();
        $poolUnit = Unit::where('title', 'Pool Management Section')->where('division_id', $directReportsDivision->id)->first();

        // 1. AGF Registry
        $this->createUser('agf.registry@oagf.gov.ng', 'AGF Registry Tester', 'AGF record manager');

        // 2. AGF
        $this->createUser('agf@oagf.gov.ng', 'AGF Tester', 'Accountant-General');

        // 3. DA Registry
        $this->createUser('da.registry@oagf.gov.ng', 'DA Registry Tester', 'registry', $adminDept->id);

        // 4. DA
        $this->createUser('da@oagf.gov.ng', 'DA Tester', 'directors', $adminDept->id);

        // 5. DD APD
        $this->createUser('dd.apd@oagf.gov.ng', 'DD APD Tester', 'Deputy Director', $adminDept->id, $apdDivision->id);

        // 6. AD Appointment
        $this->createUser('ad.appt@oagf.gov.ng', 'AD Appointment Tester', 'Assistant Director', $adminDept->id, $apdDivision->id, $apptUnit->id);

        // 7. AD Promotion
        $this->createUser('ad.promo@oagf.gov.ng', 'AD Promotion Tester', 'Assistant Director', $adminDept->id, $apdDivision->id, $promoUnit->id);

        // 8. DD TSW
        $this->createUser('dd.tsw@oagf.gov.ng', 'DD TSW Tester', 'Deputy Director', $adminDept->id, $tswDivision->id);

        // 9. AD Pool
        $this->createUser('ad.pool@oagf.gov.ng', 'AD Pool Tester', 'Assistant Director', $adminDept->id, $directReportsDivision->id, $poolUnit->id);

        $this->command->info('Phase 24 UAT accounts seeded successfully.');
    }

    private function createUser($email, $name, $roleName, $deptId = null, $divId = null, $unitId = null)
    {
        $user = User::factory()->create([
            'email' => $email,
            'first_name' => explode(' ', $name)[0],
            'last_name' => explode(' ', $name)[1] ?? 'Tester',
            'department_id' => $deptId,
            'division_id' => $divId,
            'unit_id' => $unitId
        ]);

        $role = Role::where('name', $roleName)->first();
        if ($role) {
            $user->assignRole($role);
        }
        
        return $user;
    }
}
