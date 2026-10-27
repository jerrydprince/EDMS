<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Ledger;
use App\Models\Payment;
use App\Exports\LedgerExport;
use Maatwebsite\Excel\Facades\Excel;

class LedgerController extends Controller
{
    public function index()
    {
        return inertia('Ledger/LedgerEntry');
    }

    public function getLedgers(Request $request){
        $ledgers = Ledger::query();
        if($request->search){
            $search = $request->search;
            $ledgers->where(function($query) use($search){
                $query->where('ref_no', 'LIKE', '%'.$search.'%')
                    ->orWhere('due_date', 'LIKE', '%'.$search.'%')
                    ->orWhere('narration', 'LIKE', '%'.$search.'%')
                    ->orWhere('debit', 'LIKE', '%'.$search.'%')
                    ->orWhere('credit', 'LIKE', '%'.$search.'%')
                    ->orWhere('balance', 'LIKE', '%'.$search.'%')
                    ->orWhere('status','LIKE', '%'.$search.'%');
            });
        }
        $ledgers = $ledgers->latest()->paginate(10);
        return response()->json($ledgers);
    }

    public function downloadReport(Request $request){
        // if($request->report == 'today'){
        //     $ledgers = Ledger::whereDate('created_at', now()->toDateString())->get();
        // }elseif($request->report == 'last7'){
        //      $ledgers = Ledger::whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()])->get();
        // }elseif($request->report == 'thisMonth'){
        //     $ledgers = Ledger::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->get();
        // }elseif($request->report == 'lastMonth'){
        //     $startOfLastMonth = now()->subMonth()->startOfMonth();
        //     $endOfLastMonth = now()->subMonth()->endOfMonth();
        //     $ledgers = Ledger::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->get();
        // }elseif($request->report == 'custom'){
        //     $start = $request->start;
        //     $end = $request->end;
        //     $ledgers = Ledger::whereBetween('created_at', [$start, $end])->get();
        // }else{
        //     $ledgers = Ledger::all();
        // }
      
      
        $filters = [
            'report' => $request->report,
            'start' => $request->start,
            'end' => $request->end,
        ];
        
        return Excel::download(new LedgerExport($filters), 'ledger_report.xlsx');

    }

    // public function saveLedger(Request $request)
    // {
    //     // Validate the request data
    //     $request->validate([
    //         'name' => 'required|string|max:255',
    //         'account_number' => 'required|string|max:50',
    //         'balance' => 'required|numeric',
    //         'currency' => 'required|string|max:10',
    //         'status' => 'required|string|max:20',
    //         'opening_balance' => 'required|numeric',
    //         'department' => 'nullable|string|max:100',
    //     ]);

    //     // Create a new ledger entry
    //     $ledgerEntry = new Ledger($request->all());
    //     $ledgerEntry->save();

    //     return redirect()->route('ledger')->with('success', 'Ledger entry created successfully.');
    // }

    public function generateRef(){
        //get last inserted id on ledger table
        $lastLedger = Ledger::latest()->first();
        if ($lastLedger) {
            $lastId = $lastLedger->id;
            $newId = $lastId + 1;
            $ref = 'Ref-' . str_pad($newId, 9, '0', STR_PAD_LEFT);
        } else {
            $ref = 'Ref-000000001'; // Starting reference if no entries exist
        }
        return $ref;
    }
    
    public function createLedgerEntry(Request $request)
    {
        // Validate the request data
        $request->validate([
            //'ref' => 'required|string|max:255',
            'due_date' => 'required',
            'narration' => 'required|string',
            'debit' => 'required|numeric',
            'credit' => 'required|numeric',
            'balance' => 'required|numeric',
            'workflow' => 'nullable|max:800',
        ]);

        // Create a new ledger entry
        $ledger = $request->id? Ledger::find($request->id): new Ledger();
        $ledger->ref_no = $request->id? $ledger->ref_no: $this->generateRef();
        $ledger->due_date = $request->due_date;
        $ledger->narration = $request->narration;
        $ledger->debit = $request->debit;
        $ledger->credit = $request->credit;
        $ledger->balance = $request->balance;
        $ledger->workflow = $request->workflow['id'];
        $ledger->payment_id = $request->workflow['id'];
        $ledger->save();
        //Ledger::create($request->all());
        return redirect()->to(url()->previous())->with('success', 'Ledger entry created successfully.');
    }

    public function getWorkflow()
    {
        $workflows = Payment::where('status', 'due')
            ->orWhere('status', 'overdue')
            ->orderBy('created_at', 'desc')
            ->get(['id', 'title', 'created_at']);

        $workflows->transform(function ($workflow) {
            return [
                'id' => $workflow->id,
                'name' => $workflow->title,
                'created_at' => $workflow->created_at->format('Y-m-d H:i:s'),
            ];
        });
        return response()->json($workflows);
    }

    public function getLedger($id){
        $ledger = Ledger::where('payment_id', $id)->first();
        return response()->json($ledger);
    }

    /**
     * Automated General Ledger Double-Entry Posting (Task 3.2)
     * Assistant Director (Final Accounts Unit) verifies and posts balanced IPSAS journal
     */
    public function postToGeneralLedger(Request $request)
    {
        $data = $request->validate([
            'payment_id' => 'required|exists:payments,id',
        ]);

        $payment = Payment::with(['voucher', 'recipient', 'vatVoucher'])->findOrFail($data['payment_id']);

        $glService = new \App\Services\GeneralLedgerPostingService();
        $summary = $glService->postPaymentJournal($payment, auth()->id() ?? 1);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Balanced IPSAS Double-Entry General Ledger journal posted successfully.',
                'data' => $summary
            ]);
        }

        return to_route('view-payment', $payment->id)->with([
            'message' => 'Balanced IPSAS Double-Entry General Ledger journal (' . $summary['journal_ref'] . ') posted successfully. Debits: ₦' . number_format($summary['total_debit'], 2) . ' = Credits: ₦' . number_format($summary['total_credit'], 2) . '.',
            'gl_summary' => $summary
        ]);
    }

    /**
     * Post Advance Retirement Double-Entry Journal
     */
    public function postRetirementToGeneralLedger(Request $request)
    {
        $data = $request->validate([
            'retirement_id' => 'required|exists:advances_retirements,id',
        ]);

        $retirement = \App\Models\AdvancesRetirement::with('ledger')->findOrFail($data['retirement_id']);

        $glService = new \App\Services\GeneralLedgerPostingService();
        $summary = $glService->postRetirementJournal($retirement, auth()->id() ?? 1);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Advance Retirement journal posted to General Ledger.',
                'data' => $summary
            ]);
        }

        return back()->with([
            'message' => 'Advance Retirement journal (' . $summary['journal_ref'] . ') posted to General Ledger. Folio cleared to ₦0.',
            'gl_summary' => $summary
        ]);
    }
}

