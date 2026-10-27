<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VoteBook;
use App\Models\CashBook;
use App\Models\Payment;
use App\Models\User;
use App\Exports\CashbookExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class CashBookController extends Controller
{
    public function index(){
        $votebooks = VoteBook::all();
        $votebooksGrouped = $votebooks->groupBy('account_type');
        
        $totalAllocation = $votebooks->sum('approved_allocation') ?: 0;
        $totalSpent = $votebooks->sum('actual_expenditure') ?: 0;
        $totalBalance = $votebooks->sum('balance') ?: 0;
        
        $totalCashbookEntries = CashBook::count();
        $totalDisbursed = CashBook::sum('amount') ?: 0;

        $treasuryStats = [
            'total_allocation' => $totalAllocation,
            'total_spent' => $totalSpent,
            'total_balance' => $totalBalance,
            'total_entries' => $totalCashbookEntries,
            'total_disbursed' => $totalDisbursed,
        ];

        return inertia('CashBook/CashBook', [
            'votebooks' => $votebooksGrouped,
            'allVotebooks' => $votebooks,
            'treasuryStats' => $treasuryStats,
        ]);
    }

    public function createCashBook(Request $request){
        $request->validate([
            'vote_book_id' => 'required',
            'date' => 'required',
            'voucher_no' => 'required',
            'ncoa_code' => 'required',
            'from' => 'required',
            'to' => 'required',
            'amount' => 'nullable',
        ]);

        $cashbook = new CashBook();
        $cashbook->vote_book_id = $request->vote_book_id;
        $cashbook->date = $request->date;
        $cashbook->voucher_no = $request->voucher_no;
        $cashbook->ncoa_code = $request->ncoa_code;
        $cashbook->dvbn = $request->dvbn ?: ('DVBN-' . strtoupper(substr(uniqid(), -6)));
        $cashbook->from = $request->from;
        $cashbook->to = $request->to;
        $cashbook->receipt_description = $request->receipt_description;
        $cashbook->payment_description = $request->payment_description;
        $cashbook->tsa_bank = $request->tsa_bank ?: 'CBN TSA Sub-Account';
        $cashbook->treasury_receipt = $request->treasury_receipt ?: ('TR-' . rand(100000, 999999));
        $cashbook->amount = floatval(str_replace(',', '', $request->amount ?: 0));
        $cashbook->disbursement_platform = $request->disbursement_platform ?: 'GIFMIS';
        $cashbook->save();

        return redirect()->back()->with('message', 'Cash Book entry recorded successfully.');
    }

    /**
     * Cash Office Disbursement Gateway (Task 3.1)
     * Executes payment via GIFMIS or Remita, logs credit into Cash Book,
     * and hands over file to Final Accounts Unit (Unit 11) for General Ledger posting.
     */
    public function disburseCash(Request $request)
    {
        $data = $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'disbursement_platform' => 'required|string', // GIFMIS, Remita, TSA-Direct
            'payment_reference' => 'required|string',    // Mandate No / RRR
            'tsa_bank' => 'required|string',             // CBN TSA Sub-Account
            'vote_book_id' => 'nullable|exists:vote_books,id',
            'value_date' => 'required|date',
            'comments' => 'nullable|string'
        ]);

        return DB::transaction(function () use ($data, $request) {
            $payment = Payment::with(['voucher', 'recipient'])->findOrFail($data['payment_id']);
            $voucher = $payment->voucher()->latest()->first();

            // 1. Update Voucher
            if ($voucher) {
                $voucher->disbursement_platform = $data['disbursement_platform'];
                $voucher->payment_reference = $data['payment_reference'];
                $voucher->save();
            }

            // 2. Liquidate Vote Book commitment & actual expenditure
            $voteBookId = $data['vote_book_id'] ?? ($voucher ? $voucher->vote_book_id : null) ?? VoteBook::first()?->id;
            if ($voteBookId) {
                $voteBook = VoteBook::find($voteBookId);
                if ($voteBook) {
                    $disbursedAmt = floatval($payment->gross_amount ?: ($voucher ? $voucher->amount : 0));
                    $voteBook->liquidateExpenditure($disbursedAmt);
                }
            }

            // 3. Record credit entry in cash_books
            $cashbook = new CashBook();
            $cashbook->vote_book_id = $voteBookId;
            $cashbook->payment_id = $payment->id;
            $cashbook->date = $data['value_date'];
            $cashbook->voucher_no = $voucher ? $voucher->voucher_number : ('PV-' . $payment->id);
            $cashbook->ncoa_code = $payment->ncoa_code ?? ($voucher ? $voucher->classification_code : '22020101');
            $cashbook->dvbn = $data['payment_reference'];
            $cashbook->from = 'OAGF TSA Operating Account';
            $cashbook->to = $payment->recipient ? ($payment->recipient->first_name . ' ' . $payment->recipient->last_name) : ($voucher ? $voucher->address : 'Beneficiary');
            $cashbook->payment_description = "Disbursement of NET ₦" . number_format($payment->net_amount, 2) . " via " . $data['disbursement_platform'] . " (Ref: " . $data['payment_reference'] . ") for " . $payment->title;
            $cashbook->tsa_bank = $data['tsa_bank'];
            $cashbook->treasury_receipt = $data['payment_reference'];
            $cashbook->amount = $payment->net_amount;
            $cashbook->disbursement_platform = $data['disbursement_platform'];
            $cashbook->save();

            // 4. Update Payment status & handover to Final Accounts Unit (Unit 11)
            $payment->status = 'paid';
            $payment->current_section_id = null; // Handed over from sections to Unit 11
            $payment->save();

            // Record assignment to Final Accounts Unit Head
            $faUnitHead = User::where('unit_id', 11)->where('is_unit_head', 1)->first() ?? User::where('unit_id', 11)->first();
            if ($faUnitHead) {
                DB::table('payment_assignment')->insert([
                    'payment_id' => $payment->id,
                    'recipient_id' => $faUnitHead->id,
                    'assigned_by' => auth()->id() ?? 1,
                    'role' => 'final_accounts_head',
                    'status' => 'pending',
                    'comments' => 'Payment Disbursed via ' . $data['disbursement_platform'] . ' (' . $data['payment_reference'] . '). Handed over for General Ledger Posting.',
                    'due_date' => now()->addDays(2),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Disbursement executed successfully. Cash Book credit logged and file dispatched to Final Accounts Unit.',
                    'cashbook' => $cashbook
                ]);
            }

            return to_route('view-payment', $payment->id)->with('message', 'Disbursement executed successfully. Cash Book credit logged and file dispatched to Final Accounts Unit.');
        });
    }

    public function getCashbooks(Request $request){
        $cashbooks = CashBook::query();
        if ($request->filled('vote_book_id') && $request->vote_book_id !== 'all') {
            $cashbooks->where('vote_book_id', $request->vote_book_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $cashbooks->where(function($query) use($search){
                $query->where('voucher_no', 'LIKE', '%'.$search.'%')
                ->orWhere('ncoa_code', 'LIKE', '%'.$search. '%')
                ->orWhere('dvbn', 'LIKE', '%'.$search.'%')
                ->orWhere('from', 'LIKE', '%'.$search.'%')
                ->orWhere('to', 'LIKE', '%'.$search.'%')
                ->orWhere('receipt_description', 'LIKE', '%'.$search.'%')
                ->orWhere('payment_description', 'LIKE', '%'.$search.'%')
                ->orWhere('tsa_bank', 'LIKE', '%'.$search.'%')
                ->orWhere('treasury_receipt', 'LIKE', '%'.$search.'%')
                ->orWhere('disbursement_platform', 'LIKE', '%'.$search.'%');
            });
        }

        if ($request->filled('platform') && $request->platform !== 'all') {
            $cashbooks->where('disbursement_platform', $request->platform);
        }

        if ($request->filled('type') && $request->type !== 'all') {
            if ($request->type === 'disbursement') {
                $cashbooks->where(function($q) {
                    $q->whereNotNull('payment_description')->where('payment_description', '!=', '');
                });
            } elseif ($request->type === 'receipt') {
                $cashbooks->where(function($q) {
                    $q->whereNotNull('receipt_description')->where('receipt_description', '!=', '');
                });
            }
        }

        if ($request->filled('date_range') && $request->date_range !== 'all') {
            if ($request->date_range === 'today') {
                $cashbooks->whereDate('date', now()->toDateString());
            } elseif ($request->date_range === 'week') {
                $cashbooks->where('date', '>=', now()->startOfWeek());
            } elseif ($request->date_range === 'month') {
                $cashbooks->where('date', '>=', now()->startOfMonth());
            }
        }

        // Summary calculations
        $summaryQuery = clone $cashbooks;
        $totalAmount = (clone $summaryQuery)->sum('amount') ?: 0;
        $totalCount = (clone $summaryQuery)->count();
        $todayDisbursed = (clone $summaryQuery)->whereDate('date', now()->toDateString())->sum('amount') ?: 0;

        $perPage = intval($request->input('per_page', 10));
        $paginated = $cashbooks->latest('date')->latest('id')->paginate($perPage);

        $result = $paginated->toArray();
        $result['summary'] = [
            'total_amount' => $totalAmount,
            'total_count' => $totalCount,
            'today_disbursed' => $todayDisbursed,
        ];

        return response()->json($result);
    }

    public function downloadReport(Request $request){
        $filters = [
            'report' => $request->report,
            'start' => $request->start,
            'end' => $request->end,
            'vote_book_id' => $request->vote_book_id
        ];
        
        return Excel::download(new CashbookExport($filters), 'cashbook_report.xlsx');
    }
}
