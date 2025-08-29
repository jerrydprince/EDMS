<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Ledger;
use App\Models\Payment;

class LedgerController extends Controller
{
    public function index()
    {
        return inertia('Ledger/LedgerEntry');
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
}
