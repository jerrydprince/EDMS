<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Password;
use App\Models\PasswordPolicy;
use App\Models\User;
use Hash;

class PasswordController extends Controller
{
    public function passwordReset(Request $request){
        $email = $request->email;
        $passwordpolicy = PasswordPolicy::first();
        return inertia('passwords/password-reset', compact('email', 'passwordpolicy'));
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

    public function passwordRecovery(Request $request){
        $request->validate(['email' => 'required|email']);
 
        $status = Password::sendResetLink(
            $request->only('email')
        );
     
        return $status === Password::RESET_LINK_SENT
                    ? back()->with(['status' => __($status)])
                    : back()->withErrors(['email' => __($status)]);
    }

    public function getPasswordResetPage(Request $request){
        $email = $request->email;
       return view('password-reset');
    }
}
