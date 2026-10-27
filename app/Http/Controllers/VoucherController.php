<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Voucher;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use App\Models\Payment;
use App\Models\VoteBook;
use App\Models\VATVoucher;
use App\Models\PaymentAuditQuery;
use App\Models\AdvancesLedger;
use App\Services\DigitalStampingService;
use App\Services\AdvancesManagementService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VoucherController extends Controller
{
    protected DigitalStampingService $stampingService;
    protected AdvancesManagementService $advancesService;

    public function __construct(DigitalStampingService $stampingService, AdvancesManagementService $advancesService)
    {
        $this->stampingService = $stampingService;
        $this->advancesService = $advancesService;
    }

    public function generateVoucherNumber()
    {
        $maxId = (int) Voucher::max('id');
        $nextId = $maxId + 1;
        $ref = 'VOU-' . str_pad($nextId, 9, '0', STR_PAD_LEFT);
        while (Voucher::where('voucher_number', $ref)->exists()) {
            $nextId++;
            $ref = 'VOU-' . str_pad($nextId, 9, '0', STR_PAD_LEFT);
        }
        return $ref;
    }

    public function generateVATVoucherNumber($id = 1)
    {
        $maxId = (int) VATVoucher::max('id');
        $nextId = $maxId + $id;
        $ref = 'VAT-VOU-' . str_pad($nextId, 9, '0', STR_PAD_LEFT);
        while (VATVoucher::where('voucher_number', $ref)->exists()) {
            $nextId++;
            $ref = 'VAT-VOU-' . str_pad($nextId, 9, '0', STR_PAD_LEFT);
        }
        return $ref;
    }

    /**
     * Create or update Payment Voucher
     * Generates pure PHP PDF via DomPDF (Zero Node/Browsershot dependency)
     */
    public function createVoucher(Request $request)
    {
        $request->validate([
            'deptal_no' => 'required',
            'source' => 'required',
            'date' => 'required|date',
            'head' => 'required',
            'checked_and_passed' => 'required',
            'amount' => 'required|numeric',
            'classification_code' => 'required',
            's_head' => 'required',
            'address' => 'required',
            'data_type' => 'required',
            'payment_id' => 'required|exists:payments,id',
            'vote_book_id' => 'nullable|exists:vote_books,id',
            'voucher_type' => 'nullable|string',
        ]);

        if ($request->hasFile('signature')) {
            $file = $request->file('signature');
            $filename = time() . '_' . $file->getClientOriginalName();
            $filepath = $file->storeAs('vouchers/signatures', $filename, 'public');
            $request->merge(['file_path' => $filepath]);
        }

        // Clean up previous files if updating
        $voucher = Voucher::where('payment_id', $request->payment_id)->first();
        if ($voucher) {
            if ($voucher->path && file_exists(public_path($voucher->path))) {
                @unlink(public_path($voucher->path));
            }

            if ($voucher->signature && Storage::disk('public')->exists($voucher->signature)) {
                Storage::disk('public')->delete($voucher->signature);
            }

            if (!$request->id) {
                $voucher->delete();
            }
        }

        // Create or update voucher entry
        $voucher = $request->id ? Voucher::find($request->id) : new Voucher();

        $voucher->deptal_no = $request->deptal_no;
        $voucher->source = is_array($request->source) ? ($request->source['title'] ?? '') : $request->source;
        $voucher->date = $request->date;
        $voucher->head = is_array($request->head) ? ($request->head['title'] ?? '') : $request->head;
        $voucher->checked_and_passed = $request->checked_and_passed;
        $voucher->amount = $request->amount;
        $voucher->classification_code = is_array($request->classification_code) ? ($request->classification_code['title'] ?? '') : $request->classification_code;
        $voucher->voucher_number = $request->id ? $voucher->voucher_number : $this->generateVoucherNumber();
        $voucher->s_head = is_array($request->s_head) ? ($request->s_head['title'] ?? '') : $request->s_head;
        $voucher->address = $request->address;
        $voucher->data_type = is_array($request->data_type) ? ($request->data_type['title'] ?? '') : $request->data_type;
        $voucher->payment_id = $request->payment_id;
        $voucher->voucher_type = $request->voucher_type ?? 'standard';

        if ($request->file_path) {
            $voucher->signature = $request->file_path;
        }
        $voucher->save();

        // Update Payment model amounts
        $payment = Payment::find($request->payment_id);
        if ($payment) {
            $payment->gross_amount = $voucher->amount;

            // If this is an Advances payment, deductions are exempt (0)
            if ($payment->payment_type === 'advances' || $voucher->voucher_type === 'advances') {
                $payment->payment_type = 'advances';
                $payment->total_deductions = 0;
                $payment->net_amount = $voucher->amount;

                // Sync with advances_ledgers if not already linked
                $advLedger = AdvancesLedger::where('payment_id', $payment->id)->first();
                if (!$advLedger) {
                    $this->advancesService->recordAdvance($payment, [
                        'amount_advanced' => $voucher->amount,
                        'purpose' => $payment->title,
                        'destination' => $request->address,
                    ], auth()->user()->id);
                }
            } else {
                // Capital / Contract Expenditure: Generate triad of vouchers at once:
                // 1. Main Voucher, 2. VAT Deduction Voucher, 3. WHT Deduction Voucher
                $this->generateCapitalDeductionVouchers($payment, $voucher, $request->vote_book_id);
            }
            $payment->save();
        }

        // Encumber Vote Book if selected
        if ($request->vote_book_id) {
            $voteBook = VoteBook::find($request->vote_book_id);
            if ($voteBook) {
                $voteBook->encumber($voucher->amount);
            }
        }

        // Render Main Voucher PDF using pure PHP DomPDF
        $fileName = "voucher_" . $voucher->id . '.pdf';
        $relativePath = 'voucher/' . $fileName;
        $fullPath = public_path($relativePath);

        if (!file_exists(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0777, true);
        }

        $pdf = Pdf::loadView('voucher', compact('voucher', 'payment'))->setPaper('a4', 'portrait');
        $pdf->save($fullPath);

        $voucher->path = $relativePath;
        $voucher->save();

        \App\Services\AuditTrailService::logFinance(
            'voucher_generated',
            "Payment Voucher #{$voucher->voucher_number} generated for ₦" . number_format($voucher->amount, 2),
            [
                'voucher_id' => $voucher->id,
                'voucher_number' => $voucher->voucher_number,
                'payment_id' => $voucher->payment_id,
                'amount' => $voucher->amount,
                'voucher_type' => $voucher->voucher_type,
                'head' => $voucher->head,
            ],
            $voucher,
            \App\Services\AuditTrailService::SEVERITY_NOTICE
        );

        return back()->with([
            'success' => ($payment && $payment->payment_type === 'advances')
                ? 'Staff Advance Voucher generated successfully.'
                : 'Capital Expenditure Vouchers generated successfully: Main Voucher and Deduction Vouchers (VAT & WHT) generated simultaneously.',
            'voucher' => $voucher
        ]);
    }

    /**
     * Generate Tax Withholding Schedule (VAT, WHT, Stamp Duty)
     * Pure PHP DomPDF rendering with gross contract sum calculation
     * (Fixes premature $payment->ledger dependency bug)
     */
    public function generateVATVoucher(Request $request)
    {
        $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'vote_book_id' => 'nullable|exists:vote_books,id',
            'gross_amount' => 'nullable|numeric',
            'vat' => 'nullable',
            'wht' => 'nullable',
            'stamp_duty' => 'nullable',
        ]);

        $payment = Payment::with('voucher')->findOrFail($request->payment_id);
        $voucher = $payment->voucher->first();

        // 1. Establish Gross Amount directly from Voucher or Payment (zero ledger dependency)
        $grossAmount = floatval($request->gross_amount ?? ($voucher ? $voucher->amount : ($payment->gross_amount ?? 0)));

        if ($grossAmount <= 0) {
            return back()->withErrors(['gross_amount' => 'Gross sum must be greater than zero. Please ensure the Voucher is generated first.']);
        }

        // 2. Check Advances Exemption (FR 1420 / Tax Regulations)
        $isAdvances = ($payment->payment_type === 'advances') || ($voucher && $voucher->voucher_type === 'advances');

        $percentages = [];
        if ($isAdvances) {
            $percentages = [
                'vat' => '0%',
                'wht' => '0%',
                'stamp_duty' => '0%',
            ];
        } else {
            $percentages = [
                'vat' => $request->vat ?? '7.5%',
                'wht' => $request->wht ?? '10%',
                'stamp_duty' => $request->stamp_duty ?? '1%',
            ];
        }

        try {
            DB::beginTransaction();

            // Clear previous VAT vouchers for this payment
            $oldVats = VATVoucher::where('payment_id', $payment->id)->get();
            foreach ($oldVats as $ov) {
                if ($ov->path && file_exists(public_path($ov->path))) {
                    @unlink(public_path($ov->path));
                }
                $ov->delete();
            }

            $createdVouchers = [];
            $totalDeductions = 0;
            $counter = 1;

            foreach ($percentages as $type => $percentage) {
                $rate = floatval(str_replace('%', '', (string)$percentage));
                $deductionAmount = round(($rate / 100.0) * $grossAmount, 2);
                $totalDeductions += $deductionAmount;

                $voucherNo = $this->generateVATVoucherNumber($counter);

                // Create filename
                $fileName = $type . "_" . $payment->id . '_' . time() . '.pdf';
                $relativePath = 'vat_voucher/' . $fileName;
                $fullPath = public_path($relativePath);

                if (!file_exists(dirname($fullPath))) {
                    mkdir(dirname($fullPath), 0777, true);
                }

                // Render PDF with pure DomPDF
                $pdf = Pdf::loadView('vat_voucher', [
                    'payment' => $payment,
                    'percentage' => $percentage,
                    'type' => strtoupper($type),
                    'grossSum' => $grossAmount,
                    'deductionAmount' => $deductionAmount,
                    'netAmount' => max(0, $grossAmount - $totalDeductions),
                    'voucherNo' => $voucherNo
                ])->setPaper('a4', 'portrait');

                $pdf->save($fullPath);

                $createdVouchers[] = [
                    'payment_id' => $payment->id,
                    'vote_book_id' => $request->vote_book_id,
                    'voucher_number' => $voucherNo,
                    'percentage' => (string)$percentage,
                    'amount' => $grossAmount,
                    'deduction_amount' => $deductionAmount,
                    'path' => $relativePath,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $counter++;
            }

            VATVoucher::insert($createdVouchers);

            // Update Payment with Gross, Total Deductions, and Net Amount
            $netAmount = max(0, $grossAmount - $totalDeductions);
            $payment->gross_amount = $grossAmount;
            $payment->total_deductions = $totalDeductions;
            $payment->net_amount = $netAmount;
            $payment->save();

            DB::commit();

            $vouchers = VATVoucher::where('payment_id', $payment->id)->get();

            return to_route('view-payment', $payment->id)->with([
                'success' => $isAdvances
                    ? 'Advances payment marked exempt (0% deductions applied).'
                    : 'Tax Deduction Schedule (VAT, WHT, Stamp Duty) generated successfully via pure DomPDF.',
                'voucher' => $vouchers
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error generating VAT/Tax vouchers: ' . $e->getMessage(), ['exception' => $e]);
            return to_route('view-payment', $payment->id)->withErrors([
                'error' => 'An error occurred while generating deduction schedules: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Checking Section Officer: Affix Inspection Stamp
     * Stamps rectangular inspection badge and forwards to Internal Audit
     */
    public function affixCheckingStamp(Request $request)
    {
        $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'comments' => 'nullable|string',
            'pin' => 'nullable'
        ]);

        // Validate digital signature PIN if provided
        if ($request->filled('pin')) {
            $user = auth()->user();
            if ((string)$user->approval_pin !== (string)$request->pin) {
                \App\Services\AuditTrailService::logSecurity(
                    'invalid_approval_pin_attempt',
                    "Failed checking stamp attempt: Invalid Security Approval PIN entered by {$user->first_name} {$user->last_name}",
                    ['payment_id' => $request->payment_id],
                    $user,
                    \App\Services\AuditTrailService::SEVERITY_WARNING
                );
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Invalid Security Approval PIN.'], 422);
                }
                return back()->withErrors(['pin' => 'Invalid Security Approval PIN.']);
            }
        }

        $voucher = Voucher::where('payment_id', $request->payment_id)->first();
        if (!$voucher) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Payment Voucher must be generated before applying checking stamp.'], 422);
            }
            return back()->withErrors(['voucher' => 'Payment Voucher must be generated before applying checking stamp.']);
        }

        $officer = auth()->user();
        $stampPath = $this->stampingService->generateCheckingStamp($officer, $voucher->voucher_number);

        $voucher->checking_officer_id = $officer->id;
        $voucher->checked_at = now();
        $voucher->checking_stamp_path = $stampPath;
        $voucher->checked_and_passed = 'Passed & Verified by ' . $officer->first_name . ' ' . $officer->last_name;
        $voucher->save();

        // Update payment section pointer to Internal Audit Section (ID 84)
        $payment = Payment::find($request->payment_id);
        if ($payment) {
            $payment->current_section_id = 84; // Internal Audit Section
            $payment->save();
        }

        // Re-generate Voucher PDF with embedded checking stamp
        $this->regenerateVoucherPdf($voucher, $payment);

        \App\Services\AuditTrailService::logFinance(
            'checking_stamp_affixed',
            "Digital Checking Stamp affixed to Voucher #{$voucher->voucher_number} by {$officer->first_name} {$officer->last_name}. Routed to Internal Audit.",
            [
                'voucher_id' => $voucher->id,
                'voucher_number' => $voucher->voucher_number,
                'payment_id' => $request->payment_id,
                'stamp_path' => $stampPath,
            ],
            $voucher,
            \App\Services\AuditTrailService::SEVERITY_NOTICE
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Digital Checking Stamp affixed successfully. File routed to Internal Audit Section.',
                'stamp_path' => $stampPath
            ]);
        }

        return back()->with('success', 'Checking Confirmation Stamp affixed successfully. File routed to Internal Audit Section.');
    }

    /**
     * Internal Audit Section Officer: Affix Audit Clearance Stamp
     * Stamps dual-ring seal and forwards to CPO / Cash Office
     */
    public function affixAuditStamp(Request $request)
    {
        $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'comments' => 'nullable|string',
            'pin' => 'nullable'
        ]);

        // Validate digital signature PIN if provided
        if ($request->filled('pin')) {
            $user = auth()->user();
            if ((string)$user->approval_pin !== (string)$request->pin) {
                \App\Services\AuditTrailService::logSecurity(
                    'invalid_approval_pin_attempt',
                    "Failed audit clearance attempt: Invalid Security Approval PIN entered by {$user->first_name} {$user->last_name}",
                    ['payment_id' => $request->payment_id],
                    $user,
                    \App\Services\AuditTrailService::SEVERITY_WARNING
                );
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Invalid Security Approval PIN.'], 422);
                }
                return back()->withErrors(['pin' => 'Invalid Security Approval PIN.']);
            }
        }

        $voucher = Voucher::where('payment_id', $request->payment_id)->first();
        if (!$voucher) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Payment Voucher must be generated before applying audit clearance stamp.'], 422);
            }
            return back()->withErrors(['voucher' => 'Payment Voucher must be generated before applying audit clearance stamp.']);
        }

        $auditor = auth()->user();
        $stampPath = $this->stampingService->generateAuditStamp($auditor, $voucher->voucher_number);

        $voucher->audit_officer_id = $auditor->id;
        $voucher->audited_at = now();
        $voucher->audit_stamp_path = $stampPath;
        $voucher->save();

        // Update payment section pointer to CPO / Cash Office Section (ID 85)
        $payment = Payment::find($request->payment_id);
        if ($payment) {
            $payment->current_section_id = 85; // CPO / Cash Office Section
            $payment->save();
        }

        // Re-generate Voucher PDF with embedded stamps
        $this->regenerateVoucherPdf($voucher, $payment);

        \App\Services\AuditTrailService::logFinance(
            'audit_clearance_affixed',
            "Pre-Payment Internal Audit Clearance Seal affixed to Voucher #{$voucher->voucher_number} by {$auditor->first_name} {$auditor->last_name}. Routed to Cash Office.",
            [
                'voucher_id' => $voucher->id,
                'voucher_number' => $voucher->voucher_number,
                'payment_id' => $request->payment_id,
                'stamp_path' => $stampPath,
            ],
            $voucher,
            \App\Services\AuditTrailService::SEVERITY_NOTICE
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pre-Payment Internal Audit Clearance Seal affixed successfully. File cleared and routed to Cash Office.',
                'stamp_path' => $stampPath
            ]);
        }

        return back()->with('success', 'Internal Audit Clearance Stamp affixed successfully. File cleared and routed to Cash Office.');
    }

    /**
     * Internal Audit Section: Raise Pre-Payment Audit Query
     */
    public function raiseAuditQuery(Request $request)
    {
        $data = $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'queried_to_section' => 'nullable|string',
            'query_subject' => 'nullable|string',
            'query_details' => 'nullable|string',
            'category' => 'nullable|string',
            'query_text' => 'nullable|string',
        ]);

        $query = PaymentAuditQuery::create([
            'payment_id' => $data['payment_id'],
            'queried_by' => auth()->user()->id,
            'queried_to_section' => $data['queried_to_section'] ?? 'Initiating Section',
            'query_subject' => $data['query_subject'] ?? ($data['category'] ?? 'Statutory Audit Query'),
            'query_details' => $data['query_details'] ?? ($data['query_text'] ?? 'Pre-payment audit review required.'),
            'status' => 'open'
        ]);

        $payment = Payment::find($data['payment_id']);
        if ($payment) {
            $payment->status = 'reverted';
            $payment->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Audit Query raised successfully.',
            'query' => $query
        ]);
    }

    /**
     * Get Audit Queries for a Payment
     */
    public function getAuditQueries($paymentId)
    {
        $queries = PaymentAuditQuery::where('payment_id', $paymentId)
            ->latest()
            ->get();
        return response()->json($queries);
    }

    /**
     * Respond to an open Audit Query
     */
    public function respondAuditQuery(Request $request)
    {
        $data = $request->validate([
            'query_id' => 'required|exists:payment_audit_queries,id',
            'response_details' => 'nullable|string',
            'response_text' => 'nullable|string',
        ]);

        $query = PaymentAuditQuery::findOrFail($data['query_id']);
        $query->response_details = $data['response_details'] ?? ($data['response_text'] ?? '');
        $query->responded_by = auth()->user()->id;
        $query->responded_at = now();
        $query->status = 'resolved';
        $query->save();

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Response to Audit Query submitted successfully.',
                'query' => $query
            ]);
        }

        return back()->with('message', 'Response to Audit Query submitted successfully.');
    }

    /**
     * Helper to re-render Voucher PDF with pure DomPDF
     */
    private function regenerateVoucherPdf(Voucher $voucher, $payment)
    {
        $fileName = "voucher_" . $voucher->id . '.pdf';
        $relativePath = 'voucher/' . $fileName;
        $fullPath = public_path($relativePath);

        $pdf = Pdf::loadView('voucher', compact('voucher', 'payment'))->setPaper('a4', 'portrait');
        $pdf->save($fullPath);

        $voucher->path = $relativePath;
        $voucher->save();
    }

    /**
     * Automatically generate deduction vouchers for capital expenditure
     * (Triad: 1. Main Voucher, 2. VAT Deduction Voucher, 3. WHT Deduction Voucher)
     */
    public function generateCapitalDeductionVouchers(Payment $payment, Voucher $voucher, $voteBookId = null)
    {
        $grossAmount = floatval($voucher->amount ?: ($payment->gross_amount ?? 0));
        if ($grossAmount <= 0) {
            return;
        }

        // Statutory rates for capital contracts
        $deductions = [
            'VAT' => [
                'rate_str' => '7.5%',
                'rate_val' => 0.075,
                'title' => 'Value Added Tax (VAT)',
                'payee' => 'Federal Inland Revenue Service (FIRS) - VAT Pool',
                'address' => 'Revenue House, Plot 522 Sokode Crescent, Wuse Zone 5, Abuja',
                'account' => 'CBN TSA FIRS VAT Pool Account 0020194833',
                'code' => '12020101'
            ],
            'WHT' => [
                'rate_str' => '10%',
                'rate_val' => 0.10,
                'title' => 'Withholding Tax (WHT)',
                'payee' => 'Federal Inland Revenue Service (FIRS) - WHT Pool',
                'address' => 'Revenue House, Plot 522 Sokode Crescent, Wuse Zone 5, Abuja',
                'account' => 'CBN TSA Withholding Tax Account 0020194844',
                'code' => '12020201'
            ],
            'STAMP_DUTY' => [
                'rate_str' => '1%',
                'rate_val' => 0.01,
                'title' => 'Stamp Duty Pool',
                'payee' => 'Federal Inland Revenue Service (Stamp Duties Division)',
                'address' => 'Revenue House, Plot 522 Sokode Crescent, Wuse Zone 5, Abuja',
                'account' => 'CBN TSA Stamp Duty Pool Account 0020194855',
                'code' => '12020301'
            ]
        ];

        // Clean previous deduction vouchers for this payment
        $oldVats = VATVoucher::where('payment_id', $payment->id)->get();
        foreach ($oldVats as $ov) {
            if ($ov->path && file_exists(public_path($ov->path))) {
                @unlink(public_path($ov->path));
            }
            $ov->delete();
        }

        $totalDeductions = 0;
        $counter = 1;
        $createdVouchers = [];

        foreach ($deductions as $typeKey => $info) {
            $deductionAmount = round($info['rate_val'] * $grossAmount, 2);
            $totalDeductions += $deductionAmount;

            $voucherNo = $typeKey . '-VOU-' . str_pad($payment->id, 4, '0', STR_PAD_LEFT) . '-' . str_pad($counter, 2, '0', STR_PAD_LEFT);

            $fileName = strtolower($typeKey) . "_" . $payment->id . '_' . time() . '.pdf';
            $relativePath = 'vat_voucher/' . $fileName;
            $fullPath = public_path($relativePath);

            if (!file_exists(dirname($fullPath))) {
                mkdir(dirname($fullPath), 0777, true);
            }

            // Render deduction voucher using unified Treasury Form 1 layout
            $pdf = Pdf::loadView('vat_voucher', [
                'payment' => $payment,
                'mainVoucher' => $voucher,
                'deductionInfo' => $info,
                'percentage' => $info['rate_str'],
                'type' => $typeKey,
                'grossSum' => $grossAmount,
                'deductionAmount' => $deductionAmount,
                'netAmount' => max(0, $grossAmount - $totalDeductions),
                'voucherNo' => $voucherNo
            ])->setPaper('a4', 'portrait');

            $pdf->save($fullPath);

            $createdVouchers[] = [
                'payment_id' => $payment->id,
                'vote_book_id' => $voteBookId,
                'voucher_number' => $voucherNo,
                'percentage' => $info['rate_str'],
                'amount' => $grossAmount,
                'deduction_amount' => $deductionAmount,
                'path' => $relativePath,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $counter++;
        }

        VATVoucher::insert($createdVouchers);

        $payment->gross_amount = $grossAmount;
        $payment->total_deductions = $totalDeductions;
        $payment->net_amount = max(0, $grossAmount - $totalDeductions);
        $payment->save();
    }

    public function saveModifiedPdf(Request $request)
    {
        $request->validate([
            'payment_id' => 'required',
            'file' => 'required|file',
        ]);

        $modifiedFile = $request->file('file');
        $rawPath = $request->input('target_path', '');
        $cleanPath = parse_url($rawPath, PHP_URL_PATH);
        $cleanPath = ltrim($cleanPath ?: '', '/');
        $targetPath = $cleanPath ? public_path($cleanPath) : null;

        if ($targetPath && file_exists($targetPath)) {
            $modifiedFile->move(dirname($targetPath), basename($targetPath));
            return response()->json([
                'status' => 'success',
                'message' => 'PDF updated successfully'
            ]);
        }

        if ($targetPath) {
            @mkdir(dirname($targetPath), 0777, true);
            $modifiedFile->move(dirname($targetPath), basename($targetPath));
            return response()->json([
                'status' => 'success',
                'message' => 'PDF saved successfully'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Target path not found'
        ], 400);
    }
}

