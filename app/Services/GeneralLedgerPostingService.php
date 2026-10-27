<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Ledger;
use App\Models\VATVoucher;
use App\Models\AdvancesLedger;
use App\Models\AdvancesRetirement;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class GeneralLedgerPostingService
{
    /**
     * Generate balanced double-entry General Ledger journals for a payment
     * Adheres to Nigerian Federal Treasury IPSAS Accrual Accounting standards
     *
     * @param Payment $payment
     * @param int $postedBy User ID of Assistant Director (Final Accounts)
     * @return array Posting summary with all created ledger entries
     */
    public function postPaymentJournal(Payment $payment, int $postedBy): array
    {
        return DB::transaction(function () use ($payment, $postedBy) {
            $voucher = $payment->voucher()->latest()->first();
            $journalRef = 'JNL-' . date('Ymd') . '-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT);
            $dueDate = $payment->due_date ?? Carbon::now();

            $entries = [];
            $totalDebit = 0;
            $totalCredit = 0;

            if ($payment->payment_type === 'advances') {
                // ==========================================
                // ADVANCES PAYMENT DOUBLE-ENTRY
                // Debit: Staff Advances Subsidiary Folio (Asset)
                // Credit: TSA Operating Bank Account (Outflow)
                // ==========================================
                $netAmount = floatval($payment->net_amount ?: $payment->gross_amount);

                // 1. Debit Advance Folio
                $entries[] = [
                    'payment_id' => $payment->id,
                    'ref_no' => $this->generateRefNumber(1),
                    'journal_ref' => $journalRef,
                    'subhead_code' => '12020101', // Personal Advance / Staff Imprest Asset Folio
                    'entry_type' => 'advance',
                    'due_date' => $dueDate,
                    'narration' => "Debit Staff Advance Folio (Asset): " . $payment->title,
                    'debit' => $netAmount,
                    'credit' => 0,
                    'balance' => $netAmount,
                    'workflow' => $payment->id,
                    'status' => 'processing',
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                $totalDebit += $netAmount;

                // 2. Credit TSA Bank
                $entries[] = [
                    'payment_id' => $payment->id,
                    'ref_no' => $this->generateRefNumber(2),
                    'journal_ref' => $journalRef,
                    'subhead_code' => '11010101', // TSA Central Bank Operating Account
                    'entry_type' => 'bank',
                    'due_date' => $dueDate,
                    'narration' => "Credit TSA Operating Account (Disbursed via " . ($voucher->disbursement_platform ?? 'GIFMIS') . "): " . $payment->title,
                    'debit' => 0,
                    'credit' => $netAmount,
                    'balance' => -$netAmount,
                    'workflow' => $payment->id,
                    'status' => 'processing',
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                $totalCredit += $netAmount;

            } else {
                // ==========================================
                // CAPITAL & OVERHEAD EXPENDITURE DOUBLE-ENTRY
                // Debit: Expenditure Subhead (Gross Contract Sum)
                // Credit: TSA Operating Bank Account (NET Sum)
                // Credit: FIRS VAT Account (TSA Subhead)
                // Credit: FIRS/SIRS WHT Account (TSA Subhead)
                // Credit: Stamp Duty Pool (TSA Subhead)
                // ==========================================
                $grossAmount = floatval($payment->gross_amount ?: ($voucher ? $voucher->amount : 0));
                $netAmount = floatval($payment->net_amount ?: $grossAmount);
                $ncoaCode = $payment->ncoa_code ?? ($voucher ? $voucher->classification_code : '22020101');

                // 1. Debit Expenditure Subhead (Gross)
                $counter = 1;
                $entries[] = [
                    'payment_id' => $payment->id,
                    'ref_no' => $this->generateRefNumber($counter++),
                    'journal_ref' => $journalRef,
                    'subhead_code' => $ncoaCode,
                    'entry_type' => 'expense',
                    'due_date' => $dueDate,
                    'narration' => "Debit Budget Expenditure Subhead ({$ncoaCode}): " . $payment->title,
                    'debit' => $grossAmount,
                    'credit' => 0,
                    'balance' => $grossAmount,
                    'workflow' => $payment->id,
                    'status' => 'processing',
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                $totalDebit += $grossAmount;

                // 2. Credit TSA Bank (Net Disbursed)
                $entries[] = [
                    'payment_id' => $payment->id,
                    'ref_no' => $this->generateRefNumber($counter++),
                    'journal_ref' => $journalRef,
                    'subhead_code' => '11010101', // TSA Bank
                    'entry_type' => 'bank',
                    'due_date' => $dueDate,
                    'narration' => "Credit TSA Operating Account (Net Mandate Ref: " . ($voucher->payment_reference ?? 'N/A') . "): " . $payment->title,
                    'debit' => 0,
                    'credit' => $netAmount,
                    'balance' => -$netAmount,
                    'workflow' => $payment->id,
                    'status' => 'processing',
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                $totalCredit += $netAmount;

                // 3. Credit Statutory Withholdings
                $vatVouchers = VATVoucher::where('payment_id', $payment->id)->get();
                foreach ($vatVouchers as $vv) {
                    $dedAmt = floatval($vv->deduction_amount);
                    if ($dedAmt > 0) {
                        $pType = strtolower($vv->percentage);
                        $type = 'tax';
                        $subhead = '21020101';
                        $desc = 'Tax Withholding';

                        if (str_contains($pType, '7.5') || str_contains(strtolower($vv->path ?? ''), 'vat')) {
                            $type = 'vat';
                            $subhead = '21020101'; // FIRS VAT
                            $desc = 'Credit FIRS 7.5% VAT Account';
                        } elseif (str_contains($pType, '10') || str_contains($pType, '5') || str_contains(strtolower($vv->path ?? ''), 'wht')) {
                            $type = 'wht';
                            $subhead = '21020201'; // FIRS/SIRS WHT
                            $desc = 'Credit FIRS/SIRS ' . $vv->percentage . ' WHT Account';
                        } elseif (str_contains($pType, '1') || str_contains(strtolower($vv->path ?? ''), 'stamp')) {
                            $type = 'stamp_duty';
                            $subhead = '21020301'; // Stamp Duty Pool
                            $desc = 'Credit Stamp Duty Pool 1% Account';
                        }

                        $entries[] = [
                            'payment_id' => $payment->id,
                            'ref_no' => $this->generateRefNumber($counter++),
                            'journal_ref' => $journalRef,
                            'subhead_code' => $subhead,
                            'entry_type' => $type,
                            'due_date' => $dueDate,
                            'narration' => "{$desc} ({$vv->percentage}): " . $payment->title,
                            'debit' => 0,
                            'credit' => $dedAmt,
                            'balance' => -$dedAmt,
                            'workflow' => $payment->id,
                            'status' => 'processing',
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                        $totalCredit += $dedAmt;
                    }
                }
            }

            // Verify Double-Entry Balance
            if (abs($totalDebit - $totalCredit) > 0.01) {
                throw new Exception("General Ledger Double-Entry Imbalance: Total Debits (₦{$totalDebit}) != Total Credits (₦{$totalCredit})");
            }

            // Insert all journal lines
            Ledger::insert($entries);

            // Lock and update Payment status
            $payment->status = 'paid';
            $payment->save();

            return [
                'journal_ref' => $journalRef,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'is_balanced' => true,
                'entries_count' => count($entries),
                'posted_at' => Carbon::now()->toDateTimeString()
            ];
        });
    }

    /**
     * Post balanced double-entry General Ledger journal for an Advance Retirement
     *
     * @param AdvancesRetirement $retirement
     * @param int $postedBy
     * @return array
     */
    public function postRetirementJournal(AdvancesRetirement $retirement, int $postedBy): array
    {
        return DB::transaction(function () use ($retirement, $postedBy) {
            $ledger = $retirement->ledger;
            $journalRef = 'JNL-RET-' . date('Ymd') . '-' . str_pad($retirement->id, 6, '0', STR_PAD_LEFT);
            $totalAdvanced = floatval($ledger->amount_advanced);
            $totalExpended = floatval($retirement->total_expended);
            $refundAmount = floatval($retirement->refund_amount);

            $entries = [];
            $totalDebit = 0;
            $totalCredit = 0;
            $counter = 1;

            // 1. Debit: Travel & DTA Expense Subhead (for expended sum)
            if ($totalExpended > 0) {
                $entries[] = [
                    'payment_id' => $ledger->payment_id,
                    'ref_no' => $this->generateRefNumber($counter++),
                    'journal_ref' => $journalRef,
                    'subhead_code' => '22020201', // Local Travel & Transport / DTA Expense
                    'entry_type' => 'expense',
                    'due_date' => Carbon::now(),
                    'narration' => "Debit Travel & DTA Expense (Advance Retirement #{$retirement->retirement_voucher_no}): " . $ledger->purpose,
                    'debit' => $totalExpended,
                    'credit' => 0,
                    'balance' => $totalExpended,
                    'workflow' => $ledger->payment_id,
                    'status' => 'retired',
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                $totalDebit += $totalExpended;
            }

            // 2. Debit: TSA Bank Operating Account (for unspent refund)
            if ($refundAmount > 0) {
                $entries[] = [
                    'payment_id' => $ledger->payment_id,
                    'ref_no' => $this->generateRefNumber($counter++),
                    'journal_ref' => $journalRef,
                    'subhead_code' => '11010101', // TSA Bank
                    'entry_type' => 'bank',
                    'due_date' => Carbon::now(),
                    'narration' => "Debit TSA Refund (Remita RRR: {$ledger->refund_rrr}): " . $ledger->purpose,
                    'debit' => $refundAmount,
                    'credit' => 0,
                    'balance' => $refundAmount,
                    'workflow' => $ledger->payment_id,
                    'status' => 'retired',
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                $totalDebit += $refundAmount;
            }

            // 3. Credit: Staff Advances Subsidiary Folio (Clearing the asset folio to ₦0)
            $entries[] = [
                'payment_id' => $ledger->payment_id,
                'ref_no' => $this->generateRefNumber($counter++),
                'journal_ref' => $journalRef,
                'subhead_code' => '12020101', // Advance Folio
                'entry_type' => 'advance',
                'due_date' => Carbon::now(),
                'narration' => "Credit Personal Advance Folio (Clearing to ₦0): " . $ledger->purpose,
                'debit' => 0,
                'credit' => $totalAdvanced,
                'balance' => -$totalAdvanced,
                'workflow' => $ledger->payment_id,
                'status' => 'retired',
                'created_at' => now(),
                'updated_at' => now()
            ];
            $totalCredit += $totalAdvanced;

            // Verify Double-Entry Balance
            if (abs($totalDebit - $totalCredit) > 0.01) {
                throw new Exception("Retirement Double-Entry Imbalance: Debits (₦{$totalDebit}) != Credits (₦{$totalCredit})");
            }

            Ledger::insert($entries);

            // Mark retirement status
            $retirement->status = 'posted_to_gl';
            $retirement->save();

            return [
                'journal_ref' => $journalRef,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'is_balanced' => true,
                'entries_count' => count($entries),
                'posted_at' => Carbon::now()->toDateTimeString()
            ];
        });
    }

    private function generateRefNumber($counter = 1)
    {
        $maxId = (int) Ledger::max('id');
        $nextId = $maxId + $counter;
        $ref = 'Ref-' . str_pad($nextId, 9, '0', STR_PAD_LEFT);
        while (Ledger::where('ref_no', $ref)->exists()) {
            $nextId++;
            $ref = 'Ref-' . str_pad($nextId, 9, '0', STR_PAD_LEFT);
        }
        return $ref;
    }
}
