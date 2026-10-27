<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Mail\VerificationCode;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Notifications\Registration;
use Illuminate\Validation\Rule;
use App\Models\PasswordPolicy;
use App\Models\User;
use Hash;

class RegistrationController extends Controller
{
    public function index(){
       // return view('register');
        $passwordpolicy = PasswordPolicy::first();
       return inertia('registration/register', compact('passwordpolicy'));
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
            $data =$request->except('password','password_confirmation');
            $user->notify(new Registration($data));
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

    public function UsersRegistration(Request $request){
      // dd($request->all());
      if($request->id){
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'gender' => 'required',
            'grade_level' => 'required',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($request->id)
            ],
            //'department' => 'required',
            'phone' => [
                'sometimes',
                Rule::unique('users')->ignore($request->id)
            ]
        ]);
      }else{
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'gender' => 'required',
            'grade_level' => 'required',
            'email' => 'required|email|unique:users',
            //'department' => 'required',
            'phone' => 'sometimes|unique:users'
        ]);
      }
        

        DB::transaction(function () use ($request) {
            //$code = $this->generateSixDigitCode();

            $user = $request->id? User::find($request->id) :new User();
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->gender = $request->gender;
            $user->grade_level = $request->grade_level;
            $user->designation_id = $request->designation_id;
            $user->email = $request->email;
           // $user->verification_code = $code;
           if(!$request->id){
             $user->password = Hash::make('password');
           }
           
            $user->department_id = $request->department;
            $user->division_id = $request->division;
            $user->unit_id = $request->unit;
            $user->section_id = $request->section ?: ($request->section_id ?: null);
            $user->phone = $request->phone;
            $user->is_approved = $request->is_approved;

            // Automatically set head of office flags based on designation or hierarchy placement
            if ($request->designation_id) {
                $desig = \App\Models\Designation::find($request->designation_id);
                if ($desig && $desig->is_head_of_office) {
                    if ($user->section_id) {
                        $user->is_section_head = 1;
                    } elseif ($user->unit_id) {
                        $user->is_unit_head = 1;
                    } elseif ($user->division_id) {
                        $user->is_division_head = 1;
                    } elseif ($user->department_id) {
                        $user->is_department_head = 1;
                    }
                }
            }

            $user->save();

            if($request->file){
                $fileName = time().'_'.$request->file('file')->getClientOriginalName();
                $path = $request->file('file')->move(public_path('users'), $fileName);

                $user->profile_image = 'users/'.$fileName;
                $user->save();
            }

            if($request->role){
                $user->syncRoles($request->role);
            }

            if($request->has('permissions')){
                // Compute role-level permissions to avoid polluting direct permissions table
                $rolePermIds = \Spatie\Permission\Models\Permission::whereHas('roles', function($q) use ($user) {
                    $q->whereIn('id', $user->roles->pluck('id'));
                })->pluck('id')->toArray();

                $incoming = collect($request->permissions)->flatten()->filter()->unique()->toArray();
                if (!empty($incoming)) {
                    $numericIds = array_values(array_filter($incoming, 'is_numeric'));
                    $stringNames = array_values(array_filter($incoming, fn($v) => !is_numeric($v)));

                    $perms = \Spatie\Permission\Models\Permission::where(function ($query) use ($numericIds, $stringNames) {
                        if (!empty($numericIds) && !empty($stringNames)) {
                            $query->whereIn('id', $numericIds)->orWhereIn('name', $stringNames);
                        } elseif (!empty($numericIds)) {
                            $query->whereIn('id', $numericIds);
                        } elseif (!empty($stringNames)) {
                            $query->whereIn('name', $stringNames);
                        }
                    })->get();

                    $directOverrides = $perms->whereNotIn('id', $rolePermIds);
                    $user->syncPermissions($directOverrides);
                } else {
                    $user->syncPermissions([]);
                }
            }

            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            \Illuminate\Support\Facades\Cache::flush();

            if(!$request->id){
                $data = $request->except('password', 'password_confirmation', 'file');
                $user->notify(new \App\Notifications\Registration($data));
            } else {
                // If it's an existing user being approved/updated, mark their registration notification as read
                DB::table('notifications')
                    ->where('notifiable_id', $user->id)
                    ->where('type', \App\Notifications\Registration::class)
                    ->update(['read_at' => now()]);
            }

        });

        return redirect()->back()->with('success',true);
    }

}
