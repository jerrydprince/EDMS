<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VoteBook;
use App\Models\CashBook;
use App\Exports\CashbookExport;
use Maatwebsite\Excel\Facades\Excel;

class CashBookController extends Controller
{
    public function index(){
        $votebooks = VoteBook::all();
        $votebooks = $votebooks->groupBy('account_type');
        return inertia('CashBook/CashBook', compact('votebooks'));
    }

    public function createCashBook(Request $request){
        $request->validate([
            'vote_book_id' => 'required',
            'date' => 'required',
            'voucher_no' => 'required',
            'ncoa_code' => 'required',
            'dvbn' => 'required',
            'from' => 'required',
            'to' => 'required',
            'tsa_bank' => 'required',
            'treasury_receipt' => 'required',
        ]);

        $cashbook = new CashBook();
        $cashbook->vote_book_id = $request->vote_book_id;
        $cashbook->date = $request->date;
        $cashbook->voucher_no = $request->voucher_no;
        $cashbook->ncoa_code = $request->ncoa_code;
        $cashbook->dvbn = $request->dvbn;
        $cashbook->from = $request->from;
        $cashbook->to = $request->to;
        $cashbook->receipt_description = $request->receipt_description;
        $cashbook->payment_description = $request->payment_description;
        $cashbook->tsa_bank = $request->tsa_bank;
        $cashbook->treasury_receipt = $request->treasury_receipt;
        $cashbook->save();

        return redirect()->back();
    }

    public function getCashbooks(Request $request){
        $cashbooks = CashBook::query();
        $cashbooks = $cashbooks->where('vote_book_id', $request->vote_book_id);
        if($request->search){
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
                ->orWhere('treasury_receipt', 'LIKE', '%'.$search.'%');
            });
        }
        $cashbooks = $cashbooks->paginate(10);
        return response()->json($cashbooks);
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
