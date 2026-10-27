<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('cash_books')) {
            Schema::table('cash_books', function (Blueprint $table) {
                if (!Schema::hasColumn('cash_books', 'amount')) {
                    $table->decimal('amount', 15, 2)->nullable()->after('treasury_receipt');
                }
                if (!Schema::hasColumn('cash_books', 'payment_id')) {
                    $table->unsignedBigInteger('payment_id')->nullable()->index()->after('vote_book_id');
                }
                if (!Schema::hasColumn('cash_books', 'disbursement_platform')) {
                    $table->string('disbursement_platform')->nullable()->after('amount');
                }
            });
        }

        if (Schema::hasTable('ledgers')) {
            Schema::table('ledgers', function (Blueprint $table) {
                if (!Schema::hasColumn('ledgers', 'subhead_code')) {
                    $table->string('subhead_code')->nullable()->after('ref_no');
                }
                if (!Schema::hasColumn('ledgers', 'entry_type')) {
                    $table->string('entry_type')->default('general')->after('subhead_code'); // expense, bank, vat, wht, stamp_duty, advance
                }
                if (!Schema::hasColumn('ledgers', 'journal_ref')) {
                    $table->string('journal_ref')->nullable()->after('entry_type');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('cash_books')) {
            Schema::table('cash_books', function (Blueprint $table) {
                $cols = ['amount', 'payment_id', 'disbursement_platform'];
                foreach ($cols as $c) {
                    if (Schema::hasColumn('cash_books', $c)) {
                        $table->dropColumn($c);
                    }
                }
            });
        }

        if (Schema::hasTable('ledgers')) {
            Schema::table('ledgers', function (Blueprint $table) {
                $cols = ['subhead_code', 'entry_type', 'journal_ref'];
                foreach ($cols as $c) {
                    if (Schema::hasColumn('ledgers', $c)) {
                        $table->dropColumn($c);
                    }
                }
            });
        }
    }
};
