<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function getAllUsers(){
        $users = User::get();
        return response()->json($users);
    }

    public function searchUsers(Request $request){
        $users = User::where('first_name', 'LIKE', '%'.$request->search.'%')
        ->orWhere('last_name', 'LIKE','%'.$request->search.'%')->select('first_name', 'last_name','profile_image', 'id')->get();
        return response()->json($users);
    }
}
