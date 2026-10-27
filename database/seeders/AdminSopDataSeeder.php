<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\PoolOfficer;
use App\Models\PoolRequisition;
use App\Models\PoolPosting;
use App\Models\DigitalStamp;
use App\Models\FileMovementCard;
use App\Models\StaffConfirmation;
use App\Models\AperSubmission;
use App\Models\WelfareClaim;
use App\Models\User;
use Carbon\Carbon;

class AdminSopDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Digital Stamps (SVG fallbacks and default configurations)
        $stamps = [
            [
                'designation_title' => 'DIRECTOR ADMINISTRATION',
                'stamp_type' => 'svg_generated',
                'ink_color' => '#1e3a8a', // Treasury Blue
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'designation_title' => 'FEDERAL ACCOUNTANTS POOL',
                'stamp_type' => 'svg_generated',
                'ink_color' => '#b45309', // Amber / Gold
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'designation_title' => 'APPOINTMENT & DISCIPLINE',
                'stamp_type' => 'svg_generated',
                'ink_color' => '#4338ca', // Indigo / Purple
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'designation_title' => 'REGISTRY INWARD / RECEIVED',
                'stamp_type' => 'svg_generated',
                'ink_color' => '#047857', // Forest Green
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($stamps as $s) {
            DigitalStamp::updateOrInsert(['designation_title' => $s['designation_title']], $s);
        }

        // 2. National Central Register of Federal Treasury Officers (GL 08 - 17)
        if (PoolOfficer::count() === 0) {
            $officers = [
                [
                    'first_name' => 'Chukwudi',
                    'last_name' => 'Okonkwo',
                    'other_names' => 'Emeka',
                    'ippis_no' => 'IPPIS-774901',
                    'staff_id' => 'OAGF/P/0892',
                    'gender' => 'male',
                    'cadre' => 'Accountant',
                    'grade_level' => 'GL 14',
                    'step' => 4,
                    'state_of_origin' => 'Anambra',
                    'lga' => 'Idemili North',
                    'geopolitical_zone' => 'South East',
                    'current_mda_name' => 'Federal Ministry of Foreign Affairs',
                    'current_mda_code' => 'MFA-002',
                    'directorate_in_mda' => 'Finance & Accounts',
                    'date_posted_to_mda' => Carbon::now()->subYears(4)->subMonths(2),
                    'tenancy_years' => 4.2,
                    'special_competencies' => ['gifmis', 'ipsas_accrual', 'foreign_mission_accounting'],
                    'confirmation_status' => 'confirmed',
                    'date_of_first_appointment' => '2010-06-15',
                    'date_of_present_appointment' => '2022-01-01',
                    'phone' => '+234 803 111 2233',
                    'email' => 'c.okonkwo@treasury.gov.ng',
                    'status' => 'due_rotation', // > 4 years
                    'remarks' => 'Completed statutory 4-year tenure. Eligible for immediate rotation to domestic line ministry.',
                ],
                [
                    'first_name' => 'Amina',
                    'last_name' => 'Bello',
                    'other_names' => 'Zainab',
                    'ippis_no' => 'IPPIS-882104',
                    'staff_id' => 'OAGF/P/1045',
                    'gender' => 'female',
                    'cadre' => 'Accountant',
                    'grade_level' => 'GL 12',
                    'step' => 3,
                    'state_of_origin' => 'Kano',
                    'lga' => 'Nasarawa',
                    'geopolitical_zone' => 'North West',
                    'current_mda_name' => 'Federal Ministry of Health',
                    'current_mda_code' => 'FMH-014',
                    'directorate_in_mda' => 'Funds & Treasury Inspection',
                    'date_posted_to_mda' => Carbon::now()->subYears(1)->subMonths(6),
                    'tenancy_years' => 1.5,
                    'special_competencies' => ['gifmis', 'tsa_mandate', 'tax_wht_vat'],
                    'confirmation_status' => 'confirmed',
                    'date_of_first_appointment' => '2014-09-01',
                    'date_of_present_appointment' => '2023-01-01',
                    'phone' => '+234 802 444 5566',
                    'email' => 'a.bello@treasury.gov.ng',
                    'status' => 'active',
                    'remarks' => 'Active performance. Stationed at FMH headquarters.',
                ],
                [
                    'first_name' => 'Babajide',
                    'last_name' => 'Adeyemi',
                    'other_names' => 'Oluwaseun',
                    'ippis_no' => 'IPPIS-910452',
                    'staff_id' => 'OAGF/P/1289',
                    'gender' => 'male',
                    'cadre' => 'Accountant',
                    'grade_level' => 'GL 15',
                    'step' => 2,
                    'state_of_origin' => 'Ogun',
                    'lga' => 'Ijebu Ode',
                    'geopolitical_zone' => 'South West',
                    'current_mda_name' => 'State House (Presidential Villa)',
                    'current_mda_code' => 'SH-001',
                    'directorate_in_mda' => 'Treasury Operations',
                    'date_posted_to_mda' => Carbon::now()->subYears(2)->subMonths(1),
                    'tenancy_years' => 2.1,
                    'special_competencies' => ['gifmis', 'ipsas_accrual', 'forensic_audit', 'tsa_mandate'],
                    'confirmation_status' => 'confirmed',
                    'date_of_first_appointment' => '2008-03-10',
                    'date_of_present_appointment' => '2021-01-01',
                    'phone' => '+234 805 777 8899',
                    'email' => 'b.adeyemi@treasury.gov.ng',
                    'status' => 'active',
                    'remarks' => 'High-level deployment at State House Treasury.',
                ],
                [
                    'first_name' => 'Fatima',
                    'last_name' => 'Shehu',
                    'other_names' => 'Garba',
                    'ippis_no' => 'IPPIS-663201',
                    'staff_id' => 'OAGF/P/0741',
                    'gender' => 'female',
                    'cadre' => 'Executive Officer (Accounts)',
                    'grade_level' => 'GL 10',
                    'step' => 5,
                    'state_of_origin' => 'Borno',
                    'lga' => 'Maiduguri',
                    'geopolitical_zone' => 'North East',
                    'current_mda_name' => 'Federal Ministry of Works',
                    'current_mda_code' => 'FMW-009',
                    'directorate_in_mda' => 'Capital Project Accounts',
                    'date_posted_to_mda' => Carbon::now()->subYears(4)->subMonths(8),
                    'tenancy_years' => 4.7,
                    'special_competencies' => ['gifmis', 'project_accounts', 'tax_wht_vat'],
                    'confirmation_status' => 'confirmed',
                    'date_of_first_appointment' => '2012-11-20',
                    'date_of_present_appointment' => '2020-01-01',
                    'phone' => '+234 809 333 4455',
                    'email' => 'f.shehu@treasury.gov.ng',
                    'status' => 'due_rotation',
                    'remarks' => 'Tenure exceeded. Recommended for recall to headquarters or redeployment.',
                ],
                [
                    'first_name' => 'Tarila',
                    'last_name' => 'Ebikabowei',
                    'other_names' => 'Pere',
                    'ippis_no' => 'IPPIS-552918',
                    'staff_id' => 'OAGF/P/1512',
                    'gender' => 'male',
                    'cadre' => 'Accountant',
                    'grade_level' => 'GL 09',
                    'step' => 2,
                    'state_of_origin' => 'Bayelsa',
                    'lga' => 'Southern Ijaw',
                    'geopolitical_zone' => 'South South',
                    'current_mda_name' => 'Nigeria Customs Service Headquarters',
                    'current_mda_code' => 'NCS-033',
                    'directorate_in_mda' => 'Revenue & Reconciliation',
                    'date_posted_to_mda' => Carbon::now()->subMonths(11),
                    'tenancy_years' => 0.9,
                    'special_competencies' => ['gifmis', 'tsa_mandate', 'reconciliation'],
                    'confirmation_status' => 'confirmed',
                    'date_of_first_appointment' => '2019-04-01',
                    'date_of_present_appointment' => '2024-01-01',
                    'phone' => '+234 813 666 7788',
                    'email' => 't.ebikabowei@treasury.gov.ng',
                    'status' => 'active',
                    'remarks' => 'Revenue reconciliation schedule officer.',
                ],
                [
                    'first_name' => 'Kabiru',
                    'last_name' => 'Danladi',
                    'other_names' => 'Musa',
                    'ippis_no' => 'IPPIS-449102',
                    'staff_id' => 'OAGF/P/1608',
                    'gender' => 'male',
                    'cadre' => 'Auditor',
                    'grade_level' => 'GL 08',
                    'step' => 1,
                    'state_of_origin' => 'Plateau',
                    'lga' => 'Jos North',
                    'geopolitical_zone' => 'North Central',
                    'current_mda_name' => 'Federal Ministry of Education',
                    'current_mda_code' => 'FME-005',
                    'directorate_in_mda' => 'Internal Audit Unit',
                    'date_posted_to_mda' => Carbon::now()->subMonths(8),
                    'tenancy_years' => 0.7,
                    'special_competencies' => ['forensic_audit', 'gifmis'],
                    'confirmation_status' => 'probation', // on probation
                    'date_of_first_appointment' => Carbon::now()->subMonths(8)->format('Y-m-d'),
                    'date_of_present_appointment' => Carbon::now()->subMonths(8)->format('Y-m-d'),
                    'phone' => '+234 816 888 9900',
                    'email' => 'k.danladi@treasury.gov.ng',
                    'status' => 'active',
                    'remarks' => 'Newly inducted treasury auditor on statutory probation.',
                ],
            ];

            foreach ($officers as $off) {
                PoolOfficer::create($off);
            }
        }

        // 3. MDA Requisitions (Appendix C)
        if (PoolRequisition::count() === 0) {
            $req1 = PoolRequisition::create([
                'requisition_no' => 'REQ/OAGF/2026/001',
                'mda_name' => 'Federal Ministry of Aviation & Aerospace Development',
                'mda_code' => 'FMA-018',
                'letter_ref' => 'FMA/ADM/ACC/VOL.II/142',
                'letter_date' => Carbon::now()->subDays(3),
                'justification' => 'Request for posting of two (2) senior accountants to head the GIFMIS expenditure commitment and TSA mandate desks following recent restructuring.',
                'grade_level_required' => 'GL 13',
                'number_required' => 2,
                'special_skills_required' => ['gifmis', 'tsa_mandate'],
                'has_letterhead' => true,
                'has_accommodation' => true,
                'status' => 'pending',
                'created_by' => 1,
            ]);

            $req2 = PoolRequisition::create([
                'requisition_no' => 'REQ/OAGF/2026/002',
                'mda_name' => 'National Information Technology Development Agency (NITDA)',
                'mda_code' => 'NITDA-007',
                'letter_ref' => 'NITDA/HQ/FIN/2026/89',
                'letter_date' => Carbon::now()->subDays(1),
                'justification' => 'Urgent replacement required for retired Chief Accountant. Competency in donor funding and IPSAS accrual required.',
                'grade_level_required' => 'GL 14',
                'number_required' => 1,
                'special_skills_required' => ['ipsas_accrual', 'donor_projects'],
                'has_letterhead' => true,
                'has_accommodation' => true,
                'status' => 'shortlisted',
                'created_by' => 1,
            ]);
        }

        // 4. Sample File Movement Card (Appendix A)
        if (FileMovementCard::count() === 0) {
            FileMovementCard::create([
                'file_no' => 'OAGF/ADMIN/APD/2026/CONF/089',
                'subject' => 'Recommendation on Senior Staff Promotion Briefs (GL 12-14)',
                'from_office' => 'Office of the Assistant Director (Promotion)',
                'to_office' => 'Office of the Deputy Director (APD)',
                'releasing_officer_id' => 1,
                'receiving_officer_id' => 43, // Director Admin / DD
                'date_time_out' => Carbon::now()->subHours(18),
                'date_time_in' => Carbon::now()->subHours(17),
                'is_treated' => false,
                'holding_hours' => 17,
                'sla_breached' => false,
                'remarks' => 'Inward docket logged under Appendix B. Awaiting vetting within 24-hr table rule.',
            ]);

            FileMovementCard::create([
                'file_no' => 'OAGF/ADMIN/POOL/REQ/2026/014',
                'subject' => 'Posting Memo: Federal Ministry of Aviation Requisition',
                'from_office' => 'Office of the Assistant Director (Pool Management)',
                'to_office' => 'Office of the Director of Administration',
                'releasing_officer_id' => 1,
                'receiving_officer_id' => 43,
                'date_time_out' => Carbon::now()->subHours(6),
                'date_time_in' => Carbon::now()->subHours(5),
                'is_treated' => false,
                'holding_hours' => 5,
                'sla_breached' => false,
                'remarks' => 'Candidate shortlist attached for DA approval pursuant to delegated powers GL 06-13.',
            ]);
        }

        // 5. Staff Confirmations (PSR 020301 - 24-Month Countdown & 3-Month Alert)
        if (StaffConfirmation::count() === 0) {
            $testUsers = User::where('id', '!=', 43)->take(4)->get();
            if ($testUsers->count() > 0) {
                // Officer 1: Approaching 24 months (22 months in - 3-month alert active)
                StaffConfirmation::create([
                    'user_id' => $testUsers[0]->id,
                    'appointment_date' => Carbon::now()->subMonths(22)->format('Y-m-d'),
                    'confirmation_due_date' => Carbon::now()->addMonths(2)->format('Y-m-d'),
                    'alert_date' => Carbon::now()->subMonths(1)->format('Y-m-d'),
                    'status' => 'probation',
                    'recommendation_notes' => '3-month advance trigger: Officer completed compulsory confirmation exams; vetting report in progress.',
                ]);

                if (isset($testUsers[1])) {
                    // Officer 2: Overdue for confirmation
                    StaffConfirmation::create([
                        'user_id' => $testUsers[1]->id,
                        'appointment_date' => Carbon::now()->subMonths(26)->format('Y-m-d'),
                        'confirmation_due_date' => Carbon::now()->subMonths(2)->format('Y-m-d'),
                        'alert_date' => Carbon::now()->subMonths(5)->format('Y-m-d'),
                        'status' => 'recommended',
                        'recommendation_notes' => 'Passed ASCON examination with credit. Recommended for Gazetting by Departmental Committee.',
                    ]);
                }

                if (isset($testUsers[2])) {
                    // Officer 3: Early probation (10 months in)
                    StaffConfirmation::create([
                        'user_id' => $testUsers[2]->id,
                        'appointment_date' => Carbon::now()->subMonths(10)->format('Y-m-d'),
                        'confirmation_due_date' => Carbon::now()->addMonths(14)->format('Y-m-d'),
                        'alert_date' => Carbon::now()->addMonths(11)->format('Y-m-d'),
                        'status' => 'probation',
                        'recommendation_notes' => 'Regular probation period in progress. No adverse reports.',
                    ]);
                }
            }
        }

        // 6. APER Submissions & Appendix D Vetting Ledger
        if (AperSubmission::count() === 0) {
            $testUsers = User::take(3)->get();
            $year = (int) date('Y');
            if (isset($testUsers[0])) {
                AperSubmission::create([
                    'user_id' => $testUsers[0]->id,
                    'year' => $year,
                    'grade_level' => 'GL 12',
                    'reporting_officer_id' => 43,
                    'overall_score' => 88.5,
                    'rating_category' => 'Outstanding',
                    'recommendation' => 'Accelerated Promotion',
                    'is_checklist_verified' => true,
                    'status' => 'vetted',
                ]);
            }

            if (isset($testUsers[1])) {
                AperSubmission::create([
                    'user_id' => $testUsers[1]->id,
                    'year' => $year,
                    'grade_level' => 'GL 10',
                    'reporting_officer_id' => 43,
                    'overall_score' => 74.0,
                    'rating_category' => 'Very Good',
                    'recommendation' => 'Promotable',
                    'is_checklist_verified' => true,
                    'status' => 'vetted',
                ]);
            }
        }

        // 7. Staff Welfare Claims (48-Hour SLA Watchdog)
        if (WelfareClaim::count() === 0) {
            $testUsers = User::take(2)->get();
            if (isset($testUsers[0])) {
                WelfareClaim::create([
                    'user_id' => $testUsers[0]->id,
                    'claim_type' => 'burial',
                    'amount_requested' => 350000.00,
                    'amount_approved' => 350000.00,
                    'justification' => 'Statutory bereavement allowance for deceased officer spouse in line with Treasury Circular OAGF/TSW/2026/08.',
                    'sla_deadline' => Carbon::now()->addHours(18),
                    'status' => 'approved',
                    'approved_by' => 43,
                ]);
            }

            if (isset($testUsers[1])) {
                WelfareClaim::create([
                    'user_id' => $testUsers[1]->id,
                    'claim_type' => 'medical',
                    'amount_requested' => 180000.00,
                    'amount_approved' => 0.00,
                    'justification' => 'Emergency hospital admission co-pay assistance supported by Federal Medical Centre clinical report.',
                    'sla_deadline' => Carbon::now()->addHours(34),
                    'status' => 'pending',
                ]);
            }
        }
    }
}
