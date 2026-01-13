<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organisation;
use App\Models\PasswordPolicy;
use App\Models\UserSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;

class SettingsController extends Controller
{
    //profile
    public function settings(){
        $user = auth()->user();
        $user->email_password = $user->email_password ? Crypt::decrypt($user->email_password) : null;
        return inertia('dashboard/settings/Settings', compact('user'));
    }

    public function organisation(){
        $organisation = Organisation::first();
        return inertia('dashboard/settings/Organisation', compact('organisation'));
    }

    public function getOrganisation(){
        $organisation = Organisation::first();
        return response()->json($organisation);
    }

    public function saveOrganisation(Request $request){
        $request->validate([
            'company_name' => 'required',
            'email' => 'required|email',
            'phone' =>'required|numeric',
            'file' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ]);

        $logo = "";
        if($request->file('file')){
            $image = $request->file('file');
            $imageName = time() . '.' . $image->extension();
            $path = 'organisation'.'/'.$imageName;
            $logo = $path;
            $image->move(public_path('organisation'), $imageName);
        }

        $organisation = Organisation::first();
        
        $org = $organisation? $organisation: new Organisation();
        $org->company_name = $request->company_name;
        $org->email = $request->email;
        $org->phone = $request->phone;
        if($logo){
            $org->logo = $logo;
        }
        $org->primary_color = $request->primary_color;
        $org->secondary_color = $request->secondary_color;
        $org->save();

        return redirect()->back();
    }

    //save password policies
    public function passwordPolicies(Request $request){
        $pwd_policy =  PasswordPolicy::first();
        $pwd = $pwd_policy? $pwd_policy: new PasswordPolicy();
        $pwd->number_of_characters = $request->number_of_characters;
        $pwd->uppercase = $request->uppercase;
        $pwd->lowercase = $request->lowercase;
        $pwd->special_character = $request->special_character;
        $pwd->number = $request->number;
        $pwd->password_request_notification = $request->password_request_notification;
        $pwd->password_change_notification = $request->password_change_notification;
        $pwd->prevent_password_reusing = $request->prevent_password_reusing;
        $pwd->save();
        return redirect()->back();
    }

    public function getPasswordPolicies(){
        $pwd = PasswordPolicy::first();
        return response()->json($pwd);
    }

    public function getUserSession(){
        //$user_session = UserSession::where('user_id',auth()->user()->id)->get();
        $session = DB::table('sessions')->where('user_id', auth()->user()->id)->get()->map(function($item){
            return
                [
                    'browser' => unserialize(base64_decode($item->payload))['browser'],
                    'location' => unserialize(base64_decode($item->payload))['location'],
                    'ip_address' => $item->ip_address,
                    'last_activity' => \Carbon\Carbon::createFromTimestamp($item->last_activity)->diffForHumans(),
                    'id' => $item->id,
                ];
            
        });
        // $data = [
        //     'browser' => unserialize(base64_decode($session->payload))['browser'],
        //     'location' => '',
        //     'ip' => $session->ip_address,
        //     'last_activity' => $session->last_activity
        // ];

        return response()->json($session);
    }

    public function deleteUserSession($id){
        $session = DB::table('sessions')->where('id',$id)->delete();
        return response()->json(['success'=>true]);
    }

    public function logOutAllOtherDevices(){
        $id = session()->getId();
        $session = DB::table('sessions')
            ->where('user_id', auth()->user()->id)
            ->whereNot('id', $id)
            ->delete();
        return response()->json(['success'=>true]);
    }

    public function enableTwoFactorAuthentication(){
        // $user = User::find($id);
        // $user->is_two_factor_enabled = true;
        // $user->save();
        $user = auth()->user();
        $user->enableTwoFactorAuthentication();
        return redirect()->back();
    }

    public function disableTwoFactorAuthentication($id){
        $user = User::find($id);
        $user->is_two_factor_enabled = false;
        $user->save();
        return redirect()->back();
    }

    public function twoFA(){
        return inertia('dashboard/2fa');
    }

    public function updateUser2FA(){
        $user = auth()->user();
        $user->is_two_factor_enabled = true;
        $user->save();
        $intendedUrl = session()->get('url.intended', redirect()->back());
        $email =  Crypt::encrypt(auth()->user()->email);
        dd(['url'=> $intendedUrl, 'email'=>$email]);
        return response()->json(['url'=> $intendedUrl, 'email'=>$email]);
    }

}
