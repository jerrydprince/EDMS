<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ensure Fauziyat Musa (User 32) is Assistant Director leading Finance & Accounts Unit (Unit 61)
        $user32 = User::find(32);
        if ($user32) {
            $user32->department_id = 3;
            $user32->division_id = 5;
            $user32->unit_id = 61;
            $user32->section_id = null;
            $user32->is_unit_head = 1;
            $user32->is_section_head = 0;
            $user32->save();

            $adRole = Role::where('name', 'assistant director')->first();
            if ($adRole && !$user32->hasRole('assistant director')) {
                $user32->assignRole($adRole);
            }
        }

        // 2. Assign Chief Accountants to Operational Sections under Unit 61:
        // Section 81: Other Charges Section -> User 27 (JESSICA UME'EZEOKE)
        $user27 = User::find(27);
        if ($user27) {
            $user27->department_id = 3;
            $user27->division_id = 5;
            $user27->unit_id = 61;
            $user27->section_id = 81;
            $user27->is_section_head = 1;
            $user27->save();
        }

        // Section 82: Advances Section -> User 28 (AYUBA USA)
        $user28 = User::find(28);
        if ($user28) {
            $user28->department_id = 3;
            $user28->division_id = 5;
            $user28->unit_id = 61;
            $user28->section_id = 82;
            $user28->is_section_head = 1;
            $user28->save();
        }

        // Section 83: Checking Section -> User 21 (Elijah Elem)
        $user21 = User::find(21);
        if ($user21) {
            $user21->department_id = 3;
            $user21->division_id = 5;
            $user21->unit_id = 61;
            $user21->section_id = 83;
            $user21->is_section_head = 1;
            $user21->save();
        }

        // Section 84: Internal Audit Section -> User 18 (TOSIN ADE)
        $user18 = User::find(18);
        if ($user18) {
            $user18->department_id = 3;
            $user18->division_id = 5;
            $user18->unit_id = 61;
            $user18->section_id = 84;
            $user18->is_section_head = 1;
            $user18->save();
        }

        // Section 85: CPO / Cash Office Section -> User 17 (Lawrence Agbata)
        $user17 = User::find(17);
        if ($user17) {
            $user17->department_id = 3;
            $user17->division_id = 5;
            $user17->unit_id = 61;
            $user17->section_id = 85;
            $user17->is_section_head = 1;
            $user17->save();
        }

        // Unit 11: Final Accounts Unit Head -> User 2 (Jonathan Osepo)
        $user2 = User::find(2);
        if ($user2) {
            $user2->department_id = 3;
            $user2->division_id = 5;
            $user2->unit_id = 11;
            $user2->is_unit_head = 1;
            $user2->save();
        }

        // Deputy Director (Payment) -> User 33 (Ezeoke Gregory) or existing DD
        $ddUser = User::role('deputy director')->first();
        if ($ddUser) {
            $ddUser->department_id = 3;
            $ddUser->division_id = 5;
            $ddUser->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe reversible no-op
    }
};
