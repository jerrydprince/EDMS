<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VoteBook;
use App\Models\CashBook;
use Illuminate\Support\Facades\DB;

class VoteBookController extends Controller
{
    public function index()
    {
        $votebooks = VoteBook::orderBy('created_at', 'DESC')->get()->map(function ($vote) {
            $vote->hasBreakdown = $vote->account_type === 'special';
            $vote->platform = "GIFMIS";
            return $vote;
        });

        $totalAllocation = $votebooks->sum('approved_allocation') ?: $votebooks->sum('balance');
        $totalSpent = $votebooks->sum('actual_expenditure') ?: 0;
        $totalCommitted = $votebooks->sum('committed_balance') ?: 0;
        $totalAvailable = $votebooks->sum('balance') ?: 0;

        $systemStats = [
            'total_allocation' => $totalAllocation,
            'total_committed' => $totalCommitted,
            'total_spent' => $totalSpent,
            'total_available' => $totalAvailable,
            'total_votes' => $votebooks->count(),
        ];

        return inertia('VoteBook/VoteBook', compact('votebooks', 'systemStats'));
    }

    public function getVoteBookLedger(Request $request)
    {
        $id = $request->input('vote_book_id') ?: $request->input('id');
        $votebook = $id ? VoteBook::find($id) : VoteBook::first();

        if (!$votebook) {
            return response()->json([
                'votebook' => null,
                'expenditures' => [],
                'commitments' => [],
                'topups' => [],
                'stats' => null
            ]);
        }

        // 1. Expenditures (Liquidated PVs in cash_books for this vote)
        $expQuery = CashBook::where('vote_book_id', $votebook->id);

        if ($request->filled('search')) {
            $s = $request->search;
            $expQuery->where(function($q) use ($s) {
                $q->where('voucher_no', 'LIKE', "%{$s}%")
                  ->orWhere('to', 'LIKE', "%{$s}%")
                  ->orWhere('ncoa_code', 'LIKE', "%{$s}%")
                  ->orWhere('payment_description', 'LIKE', "%{$s}%");
            });
        }

        if ($request->filled('date_range') && $request->date_range !== 'all') {
            if ($request->date_range === 'today') {
                $expQuery->whereDate('date', now()->toDateString());
            } elseif ($request->date_range === 'week') {
                $expQuery->where('date', '>=', now()->startOfWeek());
            } elseif ($request->date_range === 'month') {
                $expQuery->where('date', '>=', now()->startOfMonth());
            }
        }

        $expenditures = $expQuery->orderBy('date', 'desc')->get();

        // 2. Commitments (Unpaid/in-flight vouchers matching this vote)
        $paidVouchers = DB::table('cash_books')->pluck('voucher_no')->filter()->toArray();
        $comQuery = DB::table('vouchers')
            ->leftJoin('payments', 'vouchers.payment_id', '=', 'payments.id')
            ->whereNotIn('vouchers.voucher_number', $paidVouchers)
            ->where(function($q) use ($votebook) {
                if ($votebook->ncoa_code) {
                    $q->where('vouchers.s_head', $votebook->ncoa_code);
                }
                $q->orWhere('vouchers.source', 'LIKE', '%' . $votebook->name . '%');
            });

        if ($request->filled('search')) {
            $s = $request->search;
            $comQuery->where(function($q) use ($s) {
                $q->where('vouchers.voucher_number', 'LIKE', "%{$s}%")
                  ->orWhere('payments.title', 'LIKE', "%{$s}%")
                  ->orWhere('vouchers.s_head', 'LIKE', "%{$s}%");
            });
        }

        $commitments = $comQuery->select(
                'vouchers.id',
                'vouchers.voucher_number',
                'vouchers.date',
                'vouchers.amount',
                'vouchers.s_head',
                'vouchers.source',
                'vouchers.checked_and_passed',
                'vouchers.audited_at',
                'vouchers.path',
                'payments.title as particulars',
                'payments.status as payment_status'
            )
            ->orderBy('vouchers.date', 'desc')
            ->get();

        // 3. Top-Up History
        $topups = DB::table('votebook_topup_history')
            ->where('vote_book_id', $votebook->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Summary calculations
        $actualExpenditure = $expenditures->sum('amount');
        $committedAmount = $commitments->sum('amount');
        $approvedAllocation = floatval($votebook->approved_allocation ?: $votebook->balance);
        $availableBalance = max(0, $approvedAllocation - ($actualExpenditure + $committedAmount));

        // Sync model cache
        if ($votebook->actual_expenditure != $actualExpenditure || $votebook->committed_balance != $committedAmount) {
            $votebook->actual_expenditure = $actualExpenditure;
            $votebook->committed_balance = $committedAmount;
            $votebook->available_balance = $availableBalance;
            $votebook->save();
        }

        $stats = [
            'approved_allocation' => $approvedAllocation,
            'committed_balance' => $committedAmount,
            'actual_expenditure' => $actualExpenditure,
            'available_balance' => $availableBalance,
            'balance' => $votebook->balance,
            'utilization_rate' => $approvedAllocation > 0 ? min(100, round((($actualExpenditure + $committedAmount) / $approvedAllocation) * 100)) : 0,
            'expenditure_count' => $expenditures->count(),
            'commitment_count' => $commitments->count(),
            'topup_count' => $topups->count(),
        ];

        return response()->json([
            'votebook' => $votebook,
            'expenditures' => $expenditures,
            'commitments' => $commitments,
            'topups' => $topups,
            'stats' => $stats
        ]);
    }

    public function createVoteBook(Request $request)
    {
        if (is_array($request->account_type)) {
            $typeName = $request->account_type['name'] ?? '';
            $request->merge(['account_type' => ($typeName === 'Special Account') ? 'special' : 'standard']);
        } elseif ($request->account_type === 'Standard Account') {
            $request->merge(['account_type' => 'standard']);
        } elseif ($request->account_type === 'Special Account') {
            $request->merge(['account_type' => 'special']);
        }

        $request->validate([
            'name' => 'required|string',
            'date_created'=> 'nullable|date',
            'gifmis_code' => 'required|string',
            'account_number' => 'required|string',
            'account_type' => 'required|in:standard,special',
            'balance' => [
                'required',
                'numeric',
                'regex:/^\d{1,13}(\.\d{1,2})?$/'
            ]
        ]);

        $vote = $request->id ? VoteBook::findOrFail($request->id) : new VoteBook();
        $vote->name = $request->name;
        $vote->date_created = $request->date_created ?: now();
        $vote->gifmis_code = $request->gifmis_code;
        $vote->account_number = $request->account_number;
        $vote->account_type = $request->account_type;
        $vote->balance = $request->balance;
        
        if (!$request->id || empty($vote->approved_allocation)) {
            $vote->approved_allocation = $request->approved_allocation ?: $request->balance;
            $vote->available_balance = $vote->approved_allocation;
        }
        if ($request->filled('ncoa_code')) {
            $vote->ncoa_code = $request->ncoa_code;
        }
        if ($request->filled('fund_code')) {
            $vote->fund_code = $request->fund_code;
        }
        if ($request->filled('subhead_title')) {
            $vote->subhead_title = $request->subhead_title;
        }

        $vote->save();

        return to_route('vote-book')->with('success', 'VoteBook registered successfully.');
    }

    public function voteBookTopUp(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'id'     => 'required|exists:vote_books,id'
        ]);

        try {
            DB::beginTransaction();

            $votebook = VoteBook::findOrFail($request->id);
            $amount = floatval($request->amount);

            $votebook->balance += $amount;
            $votebook->approved_allocation = ($votebook->approved_allocation ?? 0) + $amount;
            $votebook->available_balance = ($votebook->available_balance ?? 0) + $amount;
            $votebook->save();

            DB::table('votebook_topup_history')->insert([
                'vote_book_id' => $votebook->id,
                'amount'       => $amount,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            DB::commit();

            return to_route('vote-book')->with('success', 'Top-up allocation warrant recorded successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return to_route('vote-book')->with('error', 'There was an error: '.$e->getMessage());
        }
    }

    public function getVoteBooks()
    {
        $votebooks = VoteBook::latest()->get()->map(function($vote){
            $vote->platform = "GIFMIS";
            $vote->hasBreakdown = $vote->account_type === 'special';
            return $vote;
        });
        return response()->json($votebooks);
    }
}
