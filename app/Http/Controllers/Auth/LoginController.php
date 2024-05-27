<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
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

    public function userBranchDetails($request){
        if(auth()->check()){
            if(auth()->user()->unit_id){
             return [
                    'branch' => 'unit',
                    'branch_id' => auth()->user()->id,
                    'branch_name' =>auth()->user()->unit->title
             ];
            }

            if(auth()->user()->division_id){
                return [
                    'branch' => 'division',
                    'branch_id' => auth()->user()->id,
                    'branch_name' =>auth()->user()->division->title
                ];
            }

            if(auth()->user()->department_id){
                return [
                    'branch' => 'department',
                    'branch_id' => auth()->user()->id,
                    'branch_name' =>auth()->user()->department->title
                ];
            }
        }
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
                return redirect()->route('password.reset', Crypt::encryptString($request->email));
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

            session(['branch'=>$this->userBranchDetails($request)]);

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
