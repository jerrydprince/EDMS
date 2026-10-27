<?php

namespace App\Services;

use App\Models\AdvancesLedger;
use App\Models\AdvancesRetirement;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class AdvancesManagementService
{
    /**
     * FR 1420 Compliance Guard
     * Civil Service Financial Regulation 1420 strictly forbids granting a new advance
     * to any officer who has an unretired advance that has passed its retirement due date.
     *
     * @param int $userId Beneficiary Officer ID
     * @return array Compliance evaluation
     */
    public function checkFR1420Compliance(int $userId): array
    {
        $now = Carbon::now()->startOfDay();

        // 1. Check for any overdue advances
        $overdueAdvance = AdvancesLedger::where('beneficiary_user_id', $userId)
            ->where(function ($query) use ($now) {
                $query->where('status', 'overdue')
                    ->orWhere(function ($q) use ($now) {
                        $q->where('status', 'active')
                            ->whereNotNull('due_retirement_date')
                            ->where('due_retirement_date', '<', $now);
                    });
            })
            ->first();

        if ($overdueAdvance) {
            // Auto update status to overdue if still marked active
            if ($overdueAdvance->status !== 'overdue') {
                $overdueAdvance->status = 'overdue';
                $overdueAdvance->save();
            }

            $dueFormatted = $overdueAdvance->due_retirement_date ? $overdueAdvance->due_retirement_date->format('d-M-Y') : 'N/A';
            $amountFormatted = number_format($overdueAdvance->amount_advanced, 2);

            return [
                'allowed' => false,
                'violation' => 'FR 1420',
                'message' => "FR 1420 Restriction: Beneficiary has an unretired advance of ₦{$amountFormatted} (Ref #{$overdueAdvance->id}) that fell due on {$dueFormatted}. Further advances are blocked until retirement.",
                'advance' => $overdueAdvance
            ];
        }

        // 2. Count active advances (warn if officer has an existing active advance)
        $activeAdvance = AdvancesLedger::where('beneficiary_user_id', $userId)
            ->where('status', 'active')
            ->first();

        return [
            'allowed' => true,
            'has_active' => (bool)$activeAdvance,
            'message' => $activeAdvance
                ? 'Officer has 1 active non-overdue advance. Proceed with caution under FR guidelines.'
                : 'Officer is compliant with FR 1420. Eligible for new advance.',
            'active_advance' => $activeAdvance
        ];
    }

    /**
     * Record a new advance in the Advances Subsidiary Ledger
     *
     * @param Payment $payment
     * @param array $data
     * @param int $createdBy
     * @return AdvancesLedger
     */
    public function recordAdvance(Payment $payment, array $data, int $createdBy): AdvancesLedger
    {
        $startDate = isset($data['journey_start_date']) ? Carbon::parse($data['journey_start_date']) : Carbon::now();
        $endDate = isset($data['journey_end_date']) ? Carbon::parse($data['journey_end_date']) : $startDate->copy()->addDays(5);
        
        // Due date: End of journey + 7 days (FR requirement)
        $dueDate = isset($data['due_retirement_date']) 
            ? Carbon::parse($data['due_retirement_date']) 
            : $endDate->copy()->addDays(7);

        return AdvancesLedger::create([
            'payment_id' => $payment->id,
            'beneficiary_user_id' => $data['beneficiary_user_id'] ?? $payment->recipient_id,
            'advance_type' => $data['advance_type'] ?? 'touring',
            'amount_advanced' => $data['amount_advanced'] ?? $payment->gross_amount ?? 0,
            'purpose' => $data['purpose'] ?? $payment->title,
            'destination' => $data['destination'] ?? null,
            'journey_start_date' => $startDate,
            'journey_end_date' => $endDate,
            'due_retirement_date' => $dueDate,
            'status' => 'active',
            'amount_retired' => 0,
            'refund_amount' => 0,
            'created_by' => $createdBy
        ]);
    }

    /**
     * Process an Advance Retirement submission
     *
     * @param int $advancesLedgerId
     * @param array $retirementData
     * @param int $officerId
     * @return AdvancesRetirement
     */
    public function processRetirement(int $advancesLedgerId, array $retirementData, int $officerId): AdvancesRetirement
    {
        return DB::transaction(function () use ($advancesLedgerId, $retirementData, $officerId) {
            $ledger = AdvancesLedger::findOrFail($advancesLedgerId);

            $totalExpended = floatval($retirementData['total_expended']);
            $advanced = floatval($ledger->amount_advanced);

            $refundAmount = 0;
            $reimbursementAmount = 0;

            if ($totalExpended < $advanced) {
                // Officer spent less: must refund balance via TSA Remita RRR
                $refundAmount = $advanced - $totalExpended;
            } elseif ($totalExpended > $advanced) {
                // Officer spent more: entitled to reimbursement
                $reimbursementAmount = $totalExpended - $advanced;
            }

            $voucherNo = 'RET-VOU-' . str_pad($ledger->id, 7, '0', STR_PAD_LEFT) . '-' . time();

            $retirement = AdvancesRetirement::create([
                'advances_ledger_id' => $ledger->id,
                'retirement_voucher_no' => $voucherNo,
                'total_expended' => $totalExpended,
                'refund_amount' => $refundAmount,
                'reimbursement_amount' => $reimbursementAmount,
                'evidence_paths' => $retirementData['evidence_paths'] ?? [],
                'checked_by' => $retirementData['checked_by'] ?? null,
                'audited_by' => $retirementData['audited_by'] ?? null,
                'status' => $retirementData['status'] ?? 'submitted'
            ]);

            // Update ledger record
            $ledger->amount_retired = $totalExpended;
            $ledger->refund_amount = $refundAmount;
            if (isset($retirementData['refund_rrr'])) {
                $ledger->refund_rrr = $retirementData['refund_rrr'];
            }

            if (($retirementData['status'] ?? '') === 'approved' || ($retirementData['status'] ?? '') === 'completed' || ($totalExpended + $refundAmount >= $advanced)) {
                $ledger->status = 'retired';
                $ledger->retired_at = Carbon::now();
            }

            $ledger->save();

            return $retirement;
        });
    }
}
