<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class MailController extends Controller
{
    public function getMailAddresses(){
        $users = User::select('id', 'email', 'email_password')->whereNotNull('email_password')->get();
        $users = $users->map(function($user){
            return [
                'id' => $user->id,
                'email' => $user->email,
                'email_password' => decrypt($user->email_password),
            ];
        });
        return response()->json($users);
    }

    public function incomingMails(Request $request){
        // Handle incoming mails here
        Log::info('Incoming mail received', $request->all());
        Log::debug($request->all());
       return response()->json(['status' => 'success']);
    }
}

