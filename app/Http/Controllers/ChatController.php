<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chat; 
use App\Events\ChatEvent;

class ChatController extends Controller
{
    public function index(){
        return inertia('chat/chat');
    }

    public function chat(Request $request){
        $chat = Chat::create([
            'sender' => auth()->user()->id,
            'receiver' => $request->receiver,
            'message' => $request->message
        ]);

        broadcast(new ChatEvent($chat));
       
        
        //return redirect()->back();
        return response()->json(['success'=>true]);
    }
}
