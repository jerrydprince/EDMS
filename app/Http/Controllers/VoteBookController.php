<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VoteBook;
use Illuminate\Support\Facades\DB;

class VoteBookController extends Controller
{
    public function index()
    {
        $votebooks = VoteBook::orderBy('created_at', 'DESC')->get();
        $votebooks = VoteBook::get()->map(function ($vote) {
            $vote->hasBreakdown = $vote->account_type === 'special';
            $vote->platform = "GIFMIS";
            return $vote;
        });
       return inertia('VoteBook/VoteBook', compact('votebooks'));
    }

    public function createVoteBook(Request $request){

        if($request->account_type['name'] == "Standard Account"){
            $request->merge(['account_type' => 'standard']);
        }else
        if($request->account_type['name'] == "Special Account"){
            $request->merge(['account_type' => 'special']);
        }

        $request->validate([
            'name' => 'required|string',
            'date_created'=> 'required|date',
            'gifmis_code' => 'required',
            'account_number' => 'required',
            'account_type' => 'required| in:standard,special',
            'balance' => [
                'required',
                'numeric',
                'regex:/^\d{1,13}(\.\d{1,2})?$/'
            ]
        ]);

        $vote = $request->id? VoteBook::findOrFail($request->id): new VoteBook();
        $vote->name = $request->name;
        $vote->date_created = $request->date_created;
        $vote->gifmis_code = $request->gifmis_code;
        $vote->account_number = $request->account_number;
        $vote->account_type = $request->account_type;
        $vote->balance = $request->balance;
        $vote->save();

        return to_route('vote-book')->with('success', 'VoteBook created successfully.');
    }

    public function voteBookTopUp(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'id'     => 'required|exists:vote_books,id'
        ]);

        try {
            DB::beginTransaction();

            $votebook = VoteBook::findOrFail($request->id);

            $votebook->balance += $request->amount;
            $votebook->save();

            DB::table('votebook_topup_history')->insert([
                'vote_book_id' => $votebook->id,
                'amount'       => $request->amount,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            DB::commit();

            return to_route('vote-book')->with('success', 'Top-up successful!');
        } catch (\Exception $e) {
            DB::rollBack();
            return to_route('vote-book')->with('error', 'There was an error: '.$e->getMessage());
        }
    }

    public function getVoteBooks(){
        $votebooks = VoteBook::latest()->get();
        $votebooks = $votebooks->map(function($vote){
            $vote->plateform = "GIFMIS";
            return $vote;
        });
        return response()->json($votebooks);
    }

}
