<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Mail\VerificationCode;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Hash;

class RegistrationController extends Controller
{
    public function index(){
       // return view('register');
       return inertia('registration/register');
    }

    function generateSixDigitCode() {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }    

    public function register(Request $request){
       
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'gender' => 'required',
            'grade_level' => 'required',
            'email' => 'required|email|unique:users',
            'phone' => 'required|unique:users',
            'password' => 'required|confirmed',
            'password_confirmation' => 'required'
        ]);

        DB::transaction(function () use ($request) {
            $code = $this->generateSixDigitCode();

            $user = new User();
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->gender = $request->gender;
            $user->grade_level = $request->grade_level;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->verification_code = $code;
            $user->password = Hash::make($request->password);
            $user->save();

            
           // Mail::to($request->email)->send(new VerificationCode($code));
            $request->session()->put(['email'=>$request->email]);
        });

        return redirect()->back()->with('success',true);
    }

    public function resendVerificationCode(Request $request){
        $email = $request->session()->get('email');
        $code = $this->generateSixDigitCode();
        $user = User::where('email',$email)->update(['verification_code'=>$code]);
        Mail::to($email)->send(new VerificationCode($code));
        return response()->json(['success'=>true]);
    }

    public function emailVerifiation(Request $request){
        $code = $request->code1.$request->code2.$request->code3.$request->code4.$request->code5.$request->code6;
        $email = $request->session()->get('email');
        $user = User::where('email', $email)->where('verification_code',$code)->first();
        if($user){
            User::where('email', $email)->where('verification_code',$code)->update(['email_verified_at'=>\Carbon\Carbon::now()]);
            //return redirect()->back()->with('success',true);
            return response()->json(['success'=>true]);
        }else{
            //return redirect()->back()->with('success',false);
            return response()->json(['success'=>false]);
        }
    }

    public function checkMailExit(Request $request){
        $user = User::where('email', $request->email)->first();
        if(!is_null($user)){
            return response()->json(['error'=>'true','error_msg'=>'Email exist']);
        }
        return response()->json(['success'=>true]);
    }

    public function checkPhoneExist(Request $request){
        $user = User::where('phone', $request->phone)->first();
        if($user){
            return response()->json(['error'=>'true', 'error_msg'=>'Phone number exist']);
        }
        return response()->json(['success'=>true]);
    }
}
