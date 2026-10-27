<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Setup Organizational Hierarchy: Finance and Accounts Unit & Operational Sections
        $div = DB::table('divisions')->where('id', 5)->first();
        if ($div) {
            // Find or create Finance and Accounts Unit under Division 5
            $faUnit = DB::table('units')
                ->where('division_id', 5)
                ->where('title', 'Finance and Accounts Unit')
                ->first();

            if (!$faUnit) {
                $faUnitId = DB::table('units')->insertGetId([
                    'title' => 'Finance and Accounts Unit',
                    'description' => 'Operational processing unit managing Other Charges, Advances, Checking, Internal Audit, and Cash Office',
                    'division_id' => 5,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $faUnitId = $faUnit->id;
            }

            // Ensure the 5 operational sections exist under Finance and Accounts Unit
            $sections = [
                'Other Charges Section' => 'Manages Capital & Overhead payments, Vote Book commitment, ERGP/NCOA coding',
                'Advances Section' => 'Manages Staff Advances, APVs, Advances Subsidiary Ledger, and Retirements',
                'Checking Section' => 'Pre-payment examination, 3-way document matching, arithmetic checking, checking stamp',
                'Internal Audit Section' => 'Statutory pre-payment audit verification, BPP compliance, audit stamp, query dispatch',
                'CPO / Cash Office Section' => 'Treasury disbursements via GIFMIS/Remita, Cash Book entries, payment mandate slips'
            ];

            foreach ($sections as $title => $desc) {
                $exists = DB::table('sections')
                    ->where('unit_id', $faUnitId)
                    ->where('title', $title)
                    ->exists();

                if (!$exists) {
                    DB::table('sections')->insert([
                        'title' => $title,
                        'description' => $desc,
                        'unit_id' => $faUnitId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // 2. Create Advances Subsidiary Ledger Table
        if (!Schema::hasTable('advances_ledgers')) {
            Schema::create('advances_ledgers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('payment_id')->nullable()->index();
                $table->unsignedBigInteger('beneficiary_user_id')->index();
                $table->string('advance_type')->default('touring'); // touring, imprest, personal, standing
                $table->decimal('amount_advanced', 15, 2);
                $table->text('purpose')->nullable();
                $table->string('destination')->nullable();
                $table->date('journey_start_date')->nullable();
                $table->date('journey_end_date')->nullable();
                $table->date('due_retirement_date')->nullable();
                $table->string('status')->default('active'); // active, due, overdue, retired
                $table->decimal('amount_retired', 15, 2)->default(0);
                $table->decimal('refund_amount', 15, 2)->default(0);
                $table->string('refund_rrr')->nullable();
                $table->timestamp('retired_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->foreign('beneficiary_user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // 3. Create Advances Retirements Table
        if (!Schema::hasTable('advances_retirements')) {
            Schema::create('advances_retirements', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('advances_ledger_id')->index();
                $table->string('retirement_voucher_no')->unique();
                $table->decimal('total_expended', 15, 2);
                $table->decimal('refund_amount', 15, 2)->default(0);
                $table->decimal('reimbursement_amount', 15, 2)->default(0);
                $table->json('evidence_paths')->nullable();
                $table->unsignedBigInteger('checked_by')->nullable();
                $table->unsignedBigInteger('audited_by')->nullable();
                $table->string('status')->default('draft'); // draft, checked, audited, completed, queried
                $table->timestamps();

                $table->foreign('advances_ledger_id')->references('id')->on('advances_ledgers')->onDelete('cascade');
            });
        }

        // 4. Enhance Vote Books Table with NCOA & ERGP Budget Control Columns
        if (Schema::hasTable('vote_books')) {
            Schema::table('vote_books', function (Blueprint $table) {
                if (!Schema::hasColumn('vote_books', 'ncoa_code')) {
                    $table->string('ncoa_code')->nullable()->after('gifmis_code');
                }
                if (!Schema::hasColumn('vote_books', 'ergp_code')) {
                    $table->string('ergp_code')->nullable()->after('ncoa_code');
                }
                if (!Schema::hasColumn('vote_books', 'fund_code')) {
                    $table->string('fund_code')->nullable()->default('01101')->after('ergp_code');
                }
                if (!Schema::hasColumn('vote_books', 'subhead_title')) {
                    $table->string('subhead_title')->nullable()->after('fund_code');
                }
                if (!Schema::hasColumn('vote_books', 'approved_allocation')) {
                    $table->decimal('approved_allocation', 15, 2)->default(0)->after('balance');
                }
                if (!Schema::hasColumn('vote_books', 'committed_balance')) {
                    $table->decimal('committed_balance', 15, 2)->default(0)->after('approved_allocation');
                }
                if (!Schema::hasColumn('vote_books', 'available_balance')) {
                    $table->decimal('available_balance', 15, 2)->default(0)->after('committed_balance');
                }
                if (!Schema::hasColumn('vote_books', 'actual_expenditure')) {
                    $table->decimal('actual_expenditure', 15, 2)->default(0)->after('available_balance');
                }
            });
        }

        // 5. Create Payment Audit Queries Table
        if (!Schema::hasTable('payment_audit_queries')) {
            Schema::create('payment_audit_queries', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('payment_id')->index();
                $table->unsignedBigInteger('queried_by')->index();
                $table->string('queried_to_section');
                $table->string('query_subject');
                $table->text('query_details');
                $table->text('response_details')->nullable();
                $table->unsignedBigInteger('responded_by')->nullable();
                $table->timestamp('responded_at')->nullable();
                $table->string('status')->default('open'); // open, resolved, escalated
                $table->timestamps();

                $table->foreign('payment_id')->references('id')->on('payments')->onDelete('cascade');
                $table->foreign('queried_by')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // 6. Enhance Payments Table with Classification & Monetary Summaries
        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                if (!Schema::hasColumn('payments', 'payment_type')) {
                    $table->string('payment_type')->nullable()->after('status'); // advances, capital, overhead
                }
                if (!Schema::hasColumn('payments', 'gross_amount')) {
                    $table->decimal('gross_amount', 15, 2)->nullable()->after('payment_type');
                }
                if (!Schema::hasColumn('payments', 'net_amount')) {
                    $table->decimal('net_amount', 15, 2)->nullable()->after('gross_amount');
                }
                if (!Schema::hasColumn('payments', 'total_deductions')) {
                    $table->decimal('total_deductions', 15, 2)->nullable()->after('net_amount');
                }
                if (!Schema::hasColumn('payments', 'ergp_code')) {
                    $table->string('ergp_code')->nullable()->after('total_deductions');
                }
                if (!Schema::hasColumn('payments', 'ncoa_code')) {
                    $table->string('ncoa_code')->nullable()->after('ergp_code');
                }
                if (!Schema::hasColumn('payments', 'current_section_id')) {
                    $table->unsignedBigInteger('current_section_id')->nullable()->after('ncoa_code');
                }
            });
        }

        // 7. Enhance Vouchers Table with Checking, Audit Stamps & Platform References
        if (Schema::hasTable('vouchers')) {
            Schema::table('vouchers', function (Blueprint $table) {
                if (!Schema::hasColumn('vouchers', 'voucher_type')) {
                    $table->string('voucher_type')->default('standard')->after('type'); // standard, advances, retirement, vat
                }
                if (!Schema::hasColumn('vouchers', 'checking_officer_id')) {
                    $table->unsignedBigInteger('checking_officer_id')->nullable()->after('checked_and_passed');
                }
                if (!Schema::hasColumn('vouchers', 'checked_at')) {
                    $table->timestamp('checked_at')->nullable()->after('checking_officer_id');
                }
                if (!Schema::hasColumn('vouchers', 'audit_officer_id')) {
                    $table->unsignedBigInteger('audit_officer_id')->nullable()->after('checked_at');
                }
                if (!Schema::hasColumn('vouchers', 'audited_at')) {
                    $table->timestamp('audited_at')->nullable()->after('audit_officer_id');
                }
                if (!Schema::hasColumn('vouchers', 'checking_stamp_path')) {
                    $table->string('checking_stamp_path')->nullable()->after('audited_at');
                }
                if (!Schema::hasColumn('vouchers', 'audit_stamp_path')) {
                    $table->string('audit_stamp_path')->nullable()->after('checking_stamp_path');
                }
                if (!Schema::hasColumn('vouchers', 'disbursement_platform')) {
                    $table->string('disbursement_platform')->nullable()->after('audit_stamp_path'); // GIFMIS, Remita
                }
                if (!Schema::hasColumn('vouchers', 'payment_reference')) {
                    $table->string('payment_reference')->nullable()->after('disbursement_platform'); // Mandate No, RRR
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('vouchers')) {
            Schema::table('vouchers', function (Blueprint $table) {
                $cols = [
                    'voucher_type', 'checking_officer_id', 'checked_at', 'audit_officer_id',
                    'audited_at', 'checking_stamp_path', 'audit_stamp_path',
                    'disbursement_platform', 'payment_reference'
                ];
                foreach ($cols as $c) {
                    if (Schema::hasColumn('vouchers', $c)) {
                        $table->dropColumn($c);
                    }
                }
            });
        }

        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                $cols = [
                    'payment_type', 'gross_amount', 'net_amount', 'total_deductions',
                    'ergp_code', 'ncoa_code', 'current_section_id'
                ];
                foreach ($cols as $c) {
                    if (Schema::hasColumn('payments', $c)) {
                        $table->dropColumn($c);
                    }
                }
            });
        }

        Schema::dropIfExists('payment_audit_queries');

        if (Schema::hasTable('vote_books')) {
            Schema::table('vote_books', function (Blueprint $table) {
                $cols = [
                    'ncoa_code', 'ergp_code', 'fund_code', 'subhead_title',
                    'approved_allocation', 'committed_balance', 'available_balance', 'actual_expenditure'
                ];
                foreach ($cols as $c) {
                    if (Schema::hasColumn('vote_books', $c)) {
                        $table->dropColumn($c);
                    }
                }
            });
        }

        Schema::dropIfExists('advances_retirements');
        Schema::dropIfExists('advances_ledgers');
    }
};
