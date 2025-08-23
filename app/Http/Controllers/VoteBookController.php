<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VoteBook;

class VoteBookController extends Controller
{
    public function index()
    {
       return inertia('VoteBook/VoteBook');
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

        $vote = new VoteBook();
        $vote->name = $request->name;
        $vote->date_created = $request->date_created;
        $vote->gifmis_code = $request->gifmis_code;
        $vote->account_number = $request->account_number;
        $vote->account_type = $request->account_type;
        $vote->balance = $request->balance;
        $vote->save();

        return to_route('vote-book')->with('success', 'VoteBook created successfully.');
    }
}
