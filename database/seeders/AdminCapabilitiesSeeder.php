<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminCapabilitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $caps = [
            [
                'code' => 'pool_officers_census',
                'name' => 'National Central Register of Federal Treasury Officers',
                'category' => 'National Pool Management',
                'description' => 'Master database of all Federal Accountants and Auditors across MDAs nationwide with Federal Character analytics.',
                'applicable_tiers' => json_encode(['department', 'division', 'unit']),
                'default_route' => '/dashboard/administration/pool/census',
                'icon' => 'lucide:globe',
                'sort_order' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'pool_requisitions_postings',
                'name' => 'Pool Requisitions & Deployment Orders',
                'category' => 'National Pool Management',
                'description' => 'MDA requisition vetting (Appendix C), candidate matching, posting orders (OAGF/POOL/01), and assumption tracking (OAGF/POOL/02).',
                'applicable_tiers' => json_encode(['department', 'division', 'unit']),
                'default_route' => '/dashboard/administration/pool/postings',
                'icon' => 'lucide:arrow-right-left',
                'sort_order' => 51,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'appointment_confirmation',
                'name' => 'Appointment & 24-Month Confirmation Lifecycle',
                'category' => 'Administration & HR',
                'description' => 'Statutory civil service confirmation tracking with 3-month advance alerts and PSR 100302 disciplinary integration.',
                'applicable_tiers' => json_encode(['department', 'division', 'unit']),
                'default_route' => '/dashboard/administration/appointments',
                'icon' => 'lucide:user-check',
                'sort_order' => 52,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'promotion_aper_management',
                'name' => 'APER Campaign & Promotion Briefs',
                'category' => 'Administration & HR',
                'description' => 'Annual APER distribution/collection cycles (Appendix D), 2-year on grade eligibility, conversions, and FCSC briefs.',
                'applicable_tiers' => json_encode(['department', 'division', 'unit']),
                'default_route' => '/dashboard/administration/promotions',
                'icon' => 'lucide:award',
                'sort_order' => 53,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'training_welfare_management',
                'name' => 'Training Needs (TNA) & Staff Welfare',
                'category' => 'Administration & HR',
                'description' => 'Annual Training Plan budget builder, sponsorship letters, and 48-hr compassionate welfare grants.',
                'applicable_tiers' => json_encode(['department', 'division', 'unit']),
                'default_route' => '/dashboard/administration/training-welfare',
                'icon' => 'lucide:heart-handshake',
                'sort_order' => 54,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'departmental_sub_registries',
                'name' => 'Departmental Sub-Registries & Docket Desk',
                'category' => 'Administration & HR',
                'description' => 'DA, APD, TSW sub-registries with Appendix A File Movement Cards and 24-hr table SLA tracking.',
                'applicable_tiers' => json_encode(['department', 'division', 'unit']),
                'default_route' => '/dashboard/administration/registry',
                'icon' => 'lucide:folder-git-2',
                'sort_order' => 55,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        foreach ($caps as $c) {
            DB::table('system_capabilities')->updateOrInsert(['code' => $c['code']], $c);
        }

        // Now activate all these capabilities for Department 14 (Administration)
        // Find Department 14
        $adminDept = DB::table('departments')->where('id', 14)->orWhere('title', 'like', '%Administration%')->first();
        if ($adminDept) {
            foreach ($caps as $c) {
                DB::table('entity_capabilities')->updateOrInsert([
                    'entity_type' => 'department',
                    'entity_id' => $adminDept->id,
                    'capability_code' => $c['code'],
                ], [
                    'is_active' => true,
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
