<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Hash;

class LoginController extends Controller
{
    public function login(){
        return view('login');
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    // public function authorize(): bool
    // {
    //     return true;
    // }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'username' => 'required|string',
            'password' => 'required|string',
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
 
        if (Auth::attempt($credentials)) {
            //check if the user password is "password"
            if (Hash::check('password', Auth::user()->password)) {
                Auth::logout();
                return redirect()->route('password.reset');
            }
            //check if user is approved
            if(!auth()->user()->hasRole('super admin')){
                if(!auth()->user()->is_approved){
                    Auth::logout();
                    return back()->withErrors([
                        'email' => 'Please wait while we approve your account',
                    ])->onlyInput('email');
                }
            }

            $request->session()->regenerate();
            if(auth()->user()->hasRole('data entry')){
                return redirect()->intended('dashboard');
            }
            return redirect()->intended('admin');
        }
 
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    // public function authenticate(Request $request): RedirectResponse
    // {
    //     $credentials = [
    //                 'uid' => $request->username,
    //                 'password' => $request->password,
    //             ];

    //     if (Auth::attempt($credentials)) {
    //         $request->session()->regenerate();

    //         return redirect()->intended('dashboard');
    //     }

    //     return back()->withErrors([
    //             'email' => 'The provided credentials do not match our records.',
    //     ])->onlyInput('email');
    // }

    public function logout(Request $request)
    {
        Auth::logout();
    
        $request->session()->invalidate();
    
        $request->session()->regenerateToken();
    
        return redirect('/login');
    }
}
