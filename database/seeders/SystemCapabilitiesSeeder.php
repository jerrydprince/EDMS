<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemCapability;
use App\Models\EntityCapability;
use App\Models\Department;

class SystemCapabilitiesSeeder extends Seeder
{
    public function run()
    {
        $capabilities = [
            // Executive Directorate Oversight
            [
                'code' => 'executive_oversight',
                'name' => 'Directorate Executive Oversight Panel',
                'category' => 'Executive Oversight',
                'description' => 'Top-level directorate dashboard providing executive radar, file dwell heatmaps, and bottleneck monitoring.',
                'applicable_tiers' => ['department'],
                'default_route' => '/dashboard/directorates/oversight',
                'icon' => 'lucide:gauge',
                'sort_order' => 1,
            ],
            [
                'code' => 'executive_clearance',
                'name' => 'Executive Minute & Policy Clearance',
                'category' => 'Executive Oversight',
                'description' => 'Authorizes executive minuting, policy clearance, and direct approvals for files from AGF and external MDAs.',
                'applicable_tiers' => ['department', 'division'],
                'default_route' => '/dashboard/directorates/oversight',
                'icon' => 'lucide:stamp',
                'sort_order' => 2,
            ],
            [
                'code' => 'file_call_up',
                'name' => 'Emergency File Call-Up & Branch Recall',
                'category' => 'Executive Oversight',
                'description' => 'Empowers the Director or Head of Division to immediately recall any stalled docket from lower desks.',
                'applicable_tiers' => ['department', 'division'],
                'default_route' => '/dashboard/directorates/oversight',
                'icon' => 'lucide:arrow-down-left',
                'sort_order' => 3,
            ],

            // Financial Management & Treasury
            [
                'code' => 'payment_vouchers',
                'name' => 'Payment Voucher Generation & Validation',
                'category' => 'Financial Management',
                'description' => 'Preparation, classification, and certificate verification of electronic payment vouchers.',
                'applicable_tiers' => ['department', 'division', 'unit', 'section'],
                'default_route' => '/dashboard/payment',
                'icon' => 'lucide:receipt',
                'sort_order' => 10,
            ],
            [
                'code' => 'vote_book_accounting',
                'name' => 'Vote Book Commitment & Expenditure Ledger',
                'category' => 'Financial Management',
                'description' => 'Departmental budget commitment tracking, fund reservation, and vote ledger maintenance.',
                'applicable_tiers' => ['department', 'division', 'unit', 'section'],
                'default_route' => '/dashboard/vote-book',
                'icon' => 'lucide:book-open',
                'sort_order' => 11,
            ],
            [
                'code' => 'cash_book_maintenance',
                'name' => 'Cash Book & Bank Accounts Posting',
                'category' => 'Financial Management',
                'description' => 'Daily electronic cash book posting, receipt ledgering, and bank balance consolidation.',
                'applicable_tiers' => ['department', 'division', 'unit', 'section'],
                'default_route' => '/dashboard/cash-book',
                'icon' => 'lucide:landmark',
                'sort_order' => 12,
            ],
            [
                'code' => 'mandate_generation',
                'name' => 'Electronic Mandate Batch Generation',
                'category' => 'Financial Management',
                'description' => 'Compiles approved vouchers into CBN / GIFMIS electronic mandate schedules for disbursement.',
                'applicable_tiers' => ['department', 'division', 'unit', 'section'],
                'default_route' => '/dashboard/payment',
                'icon' => 'lucide:send',
                'sort_order' => 13,
            ],
            [
                'code' => 'bank_reconciliation',
                'name' => 'Bank Statement Verification & Reconciliation',
                'category' => 'Financial Management',
                'description' => 'Automated and manual reconciliation of bank statements against cash book entries.',
                'applicable_tiers' => ['department', 'division', 'unit', 'section'],
                'default_route' => '/dashboard/cash-book',
                'icon' => 'lucide:check-check',
                'sort_order' => 14,
            ],
            [
                'code' => 'tax_deductions_wht_vat',
                'name' => 'WHT & VAT Tax Schedules & Remittance',
                'category' => 'Financial Management',
                'description' => 'Calculation, deduction, and generation of withholding tax and VAT remittance vouchers.',
                'applicable_tiers' => ['department', 'division', 'unit', 'section'],
                'default_route' => '/dashboard/payment',
                'icon' => 'lucide:file-spreadsheet',
                'sort_order' => 15,
            ],

            // Audit, Inspection & Compliance
            [
                'code' => 'pre_payment_audit',
                'name' => 'Pre-Payment Voucher Examination & Clearance',
                'category' => 'Audit & Inspection',
                'description' => 'Internal audit examination of vouchers, receipts, and supporting invoices prior to mandate release.',
                'applicable_tiers' => ['department', 'division', 'unit', 'section'],
                'default_route' => '/dashboard/payment',
                'icon' => 'lucide:shield-alert',
                'sort_order' => 20,
            ],
            [
                'code' => 'post_payment_inspection',
                'name' => 'Post-Payment Inspection & Asset Tracing',
                'category' => 'Audit & Inspection',
                'description' => 'Periodic verification of value delivered, store receipts, and compliance reviews.',
                'applicable_tiers' => ['department', 'division', 'unit', 'section'],
                'default_route' => '/dashboard/payment',
                'icon' => 'lucide:search',
                'sort_order' => 21,
            ],
            [
                'code' => 'audit_query_issuance',
                'name' => 'Audit Query Issuance & Compliance Follow-up',
                'category' => 'Audit & Inspection',
                'description' => 'Formal issuance of financial observation queries to desks with mandatory explanation deadlines.',
                'applicable_tiers' => ['department', 'division', 'unit', 'section'],
                'default_route' => '/dashboard/administration/queries',
                'icon' => 'lucide:help-circle',
                'sort_order' => 22,
            ],

            // Public Procurement & Tenders
            [
                'code' => 'procurement_requisitions',
                'name' => 'Procurement Requisition Vetting',
                'category' => 'Public Procurement',
                'description' => 'Vetting departmental purchase requests against annual budget threshold ceilings.',
                'applicable_tiers' => ['department', 'division', 'unit', 'section'],
                'default_route' => '/dashboard/procurement',
                'icon' => 'lucide:shopping-cart',
                'sort_order' => 30,
            ],
            [
                'code' => 'tender_evaluation',
                'name' => 'Tender Bidding & Contractor Evaluation',
                'category' => 'Public Procurement',
                'description' => 'Tenders board document collation, financial bid analysis, and scoring summaries.',
                'applicable_tiers' => ['department', 'division', 'unit', 'section'],
                'default_route' => '/dashboard/procurement',
                'icon' => 'lucide:award',
                'sort_order' => 31,
            ],

            // Human Resources & Administration
            [
                'code' => 'nominal_roll_mgt',
                'name' => 'Departmental Nominal Roll & Postings',
                'category' => 'HR & General Admin',
                'description' => 'Management of staff roster, cadre postings, and department desk assignments.',
                'applicable_tiers' => ['department', 'division', 'unit', 'section'],
                'default_route' => '/dashboard/governance-hub',
                'icon' => 'lucide:users',
                'sort_order' => 40,
            ],
            [
                'code' => 'leave_administration',
                'name' => 'Leave Applications & Relief Officer Endorsement',
                'category' => 'HR & General Admin',
                'description' => 'Processing annual, casual, and maternity leaves with handover relief assignment.',
                'applicable_tiers' => ['department', 'division', 'unit', 'section'],
                'default_route' => '/dashboard/administration/leaves',
                'icon' => 'lucide:calendar-clock',
                'sort_order' => 41,
            ],
            [
                'code' => 'psr_disciplinary_query',
                'name' => 'Public Service Rules Disciplinary Queries',
                'category' => 'HR & General Admin',
                'description' => 'Issuance of formal PSR disciplinary queries for misconduct or absenteeism.',
                'applicable_tiers' => ['department', 'division', 'unit', 'section'],
                'default_route' => '/dashboard/administration/queries',
                'icon' => 'lucide:alert-triangle',
                'sort_order' => 42,
            ],

            // Registry, Records & EDMS
            [
                'code' => 'file_jacket_creation',
                'name' => 'Docket / File Jacket Creation & Coding',
                'category' => 'Registry & Records',
                'description' => 'Opening new physical and digital folders with formal institutional reference numbers.',
                'applicable_tiers' => ['department', 'division', 'unit', 'section'],
                'default_route' => '/dashboard/registry',
                'icon' => 'lucide:folder-plus',
                'sort_order' => 50,
            ],
            [
                'code' => 'document_scanning_upload',
                'name' => 'Digital Scanning & Electronic Archiving',
                'category' => 'Registry & Records',
                'description' => 'High-resolution document scanning, PDF indexing, and versioned file archiving.',
                'applicable_tiers' => ['department', 'division', 'unit', 'section'],
                'default_route' => '/edms',
                'icon' => 'lucide:file-up',
                'sort_order' => 51,
            ],
            [
                'code' => 'cross_branch_dispatch',
                'name' => 'Cross-Branch Transit & Chain-of-Custody Dispatch',
                'category' => 'Registry & Records',
                'description' => 'Formal physical and electronic dispatch tracking with dispatch book receipting.',
                'applicable_tiers' => ['department', 'division', 'unit', 'section'],
                'default_route' => '/dashboard/registry',
                'icon' => 'lucide:truck',
                'sort_order' => 52,
            ],
        ];

        foreach ($capabilities as $cap) {
            SystemCapability::updateOrCreate(
                ['code' => $cap['code']],
                $cap
            );
        }

        // Initialize default master capabilities for all existing departments
        $allDepartments = Department::all();
        foreach ($allDepartments as $dept) {
            // Give all departments Executive Oversight, Registry/EDMS, and HR capabilities
            $baseCodes = [
                'executive_oversight', 'executive_clearance', 'file_call_up',
                'nominal_roll_mgt', 'leave_administration', 'psr_disciplinary_query',
                'file_jacket_creation', 'document_scanning_upload', 'cross_branch_dispatch'
            ];

            // Specific additions based on department name/nature
            $titleLower = strtolower($dept->title);
            if (str_contains($titleLower, 'finance') || str_contains($titleLower, 'fund') || str_contains($titleLower, 'account') || str_contains($titleLower, 'revenue')) {
                $baseCodes = array_merge($baseCodes, [
                    'payment_vouchers', 'vote_book_accounting', 'cash_book_maintenance',
                    'mandate_generation', 'bank_reconciliation', 'tax_deductions_wht_vat',
                    'pre_payment_audit'
                ]);
            }
            if (str_contains($titleLower, 'procurement')) {
                $baseCodes = array_merge($baseCodes, [
                    'procurement_requisitions', 'tender_evaluation'
                ]);
            }
            if (str_contains($titleLower, 'audit') || str_contains($titleLower, 'inspectorate')) {
                $baseCodes = array_merge($baseCodes, [
                    'pre_payment_audit', 'post_payment_inspection', 'audit_query_issuance'
                ]);
            }

            foreach (array_unique($baseCodes) as $code) {
                EntityCapability::updateOrCreate(
                    [
                        'entity_type' => 'department',
                        'entity_id' => $dept->id,
                        'capability_code' => $code,
                    ],
                    [
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
