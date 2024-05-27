<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\User;
use Hash;

class PasswordController extends Controller
{
    public function passwordReset(Request $request){
        $email = $request->email;
        return inertia('passwords/password-reset', compact('email'));
    }

    public function ResetUserPassword(Request $request){
        $request->validate([
            'password' => 'required|confirmed',
            'password_confirmation' => 'required'
        ]);
        $email = Crypt::decryptString($request->email);
        User::where('email', $email)->update(['password'=>Hash::make($request->password)]);
        return redirect()->back()->with('success','password updated successfully');
    }
}
