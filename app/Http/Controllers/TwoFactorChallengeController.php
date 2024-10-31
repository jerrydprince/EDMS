<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TwoFactorChallengeController extends Controller
{
    public function show()
    {
        return view('auth.two-factor-challenge');
    }

    public function verify(Request $request)
    {
        $request->validate([
            '2fa_code' => 'required',
        ]);

        $user = Auth::loginUsingId(session()->get('2fa:user:id')); // Retrieve user from session

        if ($user->twoFactorChallenge($request->input('2fa_code'))) {
            session()->forget('2fa:user:id'); // Clear 2FA session once successful
            $request->session()->regenerate(); // Regenerate session
            return redirect()->intended('dashboard'); // Redirect to intended page
        }

        Auth::logout();
        return back()->withErrors(['2fa_code' => 'Invalid 2FA code.']);
    }
}
