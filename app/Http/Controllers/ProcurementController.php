<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentAssignment;
use App\Models\Procurement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProcurementController extends Controller
{
    /**
     * Display a listing of procurements.
     */
    public function index(Request $request)
    {
        $query = Procurement::with(['creator', 'evaluator', 'approver', 'lpoIssuer', 'dispatcher', 'payment'])
            ->orderBy('id', 'desc');

        if ($request->has('status') && !empty($request->status) && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('bpp_ref', 'like', "%{$search}%")
                  ->orWhere('vendor_name', 'like', "%{$search}%")
                  ->orWhere('lpo_number', 'like', "%{$search}%");
            });
        }

        $procurements = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $procurements
        ]);
    }

    /**
     * Store a new procurement requirement.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'vendor_name' => 'required|string|max:255',
            'vendor_rc_number' => 'nullable|string|max:100',
            'vendor_bank_name' => 'nullable|string|max:100',
            'vendor_account_no' => 'nullable|string|max:50',
            'estimated_amount' => 'required|numeric|min:1',
            'delivery_period_days' => 'nullable|integer|min:1',
            'terms_conditions' => 'nullable|string'
        ]);

        $amount = (float) $validated['estimated_amount'];

        // BPP 2007 Statutory Thresholds
        if ($amount <= 50000000) {
            $threshold = 'Accounting Officer';
        } elseif ($amount <= 100000000) {
            $threshold = 'Ministerial Tenders Board';
        } else {
            $threshold = 'Federal Executive Council';
        }

        $year = date('Y');
        $random = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
        $bppRef = "BPP/PROC/{$year}/{$random}";

        $procurement = Procurement::create([
            'bpp_ref' => $bppRef,
            'title' => $validated['title'],
            'vendor_name' => $validated['vendor_name'],
            'vendor_rc_number' => $validated['vendor_rc_number'] ?? null,
            'vendor_bank_name' => $validated['vendor_bank_name'] ?? null,
            'vendor_account_no' => $validated['vendor_account_no'] ?? null,
            'estimated_amount' => $amount,
            'threshold_level' => $threshold,
            'delivery_period_days' => $validated['delivery_period_days'] ?? 30,
            'terms_conditions' => $validated['terms_conditions'] ?? 'Standard Federal Government Procurement Terms apply.',
            'status' => 'draft',
            'created_by' => Auth::id() ?? 1,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Procurement requisition initiated successfully under BPP 2007 framework.',
            'data' => $procurement->load('creator')
        ], 201);
    }

    /**
     * Evaluate BPP threshold & certification.
     */
    public function evaluateBpp(Request $request, $id)
    {
        $procurement = Procurement::findOrFail($id);

        $validated = $request->validate([
            'decision' => 'required|in:approved,rejected,needs_clarification',
            'evaluation_notes' => 'required|string',
            'bpp_cert_no' => 'nullable|string|max:100'
        ]);

        // If amount requires Ministerial Tenders Board or FEC, certificate of no objection or board approval is mandatory
        if (in_array($procurement->threshold_level, ['Ministerial Tenders Board', 'Federal Executive Council'])) {
            if (empty($validated['bpp_cert_no'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Statutory BPP Certificate of 'No Objection' or Tenders Board Approval Ref is mandatory for {$procurement->threshold_level} threshold."
                ], 422);
            }
        }

        $status = $validated['decision'] === 'approved' ? 'approved' : 'draft';

        $procurement->update([
            'evaluated_by' => Auth::id() ?? 1,
            'approved_by' => $validated['decision'] === 'approved' ? (Auth::id() ?? 1) : null,
            'evaluation_notes' => $validated['evaluation_notes'],
            'bpp_cert_no' => $validated['bpp_cert_no'] ?? $procurement->bpp_cert_no,
            'status' => $status
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Procurement statutory evaluation completed. Status: {$status}.",
            'data' => $procurement->load(['evaluator', 'approver'])
        ]);
    }

    /**
     * Generate statutory Local Purchase Order (LPO).
     */
    public function generateLpo(Request $request, $id)
    {
        $procurement = Procurement::findOrFail($id);

        if (!in_array($procurement->status, ['approved', 'lpo_issued'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Procurement must be approved by Tenders Board / Accounting Officer prior to LPO issuance.'
            ], 422);
        }

        $year = date('Y');
        $lpoNumber = $procurement->lpo_number ?: "LPO/FGN/{$year}/" . str_pad($procurement->id, 5, '0', STR_PAD_LEFT);

        $procurement->update([
            'lpo_number' => $lpoNumber,
            'lpo_issued_by' => Auth::id() ?? 1,
            'status' => 'lpo_issued',
            'terms_conditions' => $request->get('terms_conditions', $procurement->terms_conditions)
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Official Local Purchase Order ({$lpoNumber}) generated successfully.",
            'data' => $procurement->load(['lpoIssuer'])
        ]);
    }

    /**
     * Dispatch LPO to Finance & Accounts for Vote Book Encumbrance & Voucher Creation.
     */
    public function dispatchToFinance(Request $request, $id)
    {
        $procurement = Procurement::findOrFail($id);

        if ($procurement->status !== 'lpo_issued') {
            return response()->json([
                'status' => 'error',
                'message' => 'LPO must be officially issued before cross-directorate dispatch to Finance & Accounts.'
            ], 422);
        }

        if ($procurement->dispatched_payment_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'This procurement file has already been dispatched to Finance & Accounts.'
            ], 422);
        }

        return DB::transaction(function() use ($procurement) {
            // Find head of Other Charges Section (Section 81) in Finance & Accounts
            $otherChargesOfficer = User::where('section_id', 81)->where('is_section_head', 1)->first()
                ?? User::where('section_id', 81)->first()
                ?? User::where('department_id', 3)->first();

            $payment = Payment::create([
                'title' => "[Procurement: {$procurement->lpo_number}] {$procurement->title}",
                'gross_amount' => $procurement->estimated_amount,
                'net_amount' => $procurement->estimated_amount,
                'status' => 'due',
                'payment_type' => 'Contract Execution',
                'current_section_id' => 81,
                'recipient_id' => $otherChargesOfficer ? $otherChargesOfficer->id : (Auth::id() ?? 1),
                'comments' => "Procurement Dossier ({$procurement->bpp_ref} - {$procurement->lpo_number}) dispatched to Finance for Vote Book Encumbrance and Voucher Preparation."
            ]);

            PaymentAssignment::create([
                'payment_id' => $payment->id,
                'assigned_by' => Auth::id() ?? 1,
                'recipient_id' => $otherChargesOfficer ? $otherChargesOfficer->id : (Auth::id() ?? 1),
                'due_date' => now()->addDays(7),
                'status' => 'pending',
                'role' => 'other charges section',
                'comments' => "Procurement Dossier ({$procurement->bpp_ref} - {$procurement->lpo_number}) dispatched to Finance for Vote Book Encumbrance and Voucher Preparation."
            ]);

            $procurement->update([
                'status' => 'dispatched_to_finance',
                'dispatched_by' => Auth::id() ?? 1,
                'dispatched_at' => now(),
                'dispatched_payment_id' => $payment->id
            ]);

            return response()->json([
                'status' => 'success',
                'message' => "Procurement LPO successfully dispatched to Finance & Accounts (Other Charges Section). Payment File #{$payment->id} opened.",
                'data' => [
                    'procurement' => $procurement->fresh(['dispatcher']),
                    'payment_id' => $payment->id,
                    'payment' => $payment
                ]
            ]);
        });
    }

    /**
     * Render official BPP-compliant Local Purchase Order (LPO) HTML view.
     */
    public function printLpo($id)
    {
        $procurement = Procurement::with(['creator', 'evaluator', 'approver', 'lpoIssuer'])->findOrFail($id);

        return view('procurement.lpo_print', compact('procurement'));
    }
}
