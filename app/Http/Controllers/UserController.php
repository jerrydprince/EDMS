<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use App\Models\Department;
use App\Models\Division;
use App\Models\Unit;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function getAllUsers(){
        $users = User::get();
        return response()->json($users);
    }

    public function searchUsers(Request $request){
        $users = User::where('first_name', 'LIKE', '%'.$request->search.'%')
        ->orWhere('last_name', 'LIKE','%'.$request->search.'%')->select('first_name', 'last_name','profile_image', 'id', 'email', 'profile_image')->get();
        return response()->json($users);
    }

    public function updateProfileImage(Request $request){
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,gif',
        ]);

        $user = User::find(auth()->user()->id);

        $image = $request->file('file');
        $imageName = time() . '.' . $image->extension();
        $path = 'users'.'/'.$imageName;
        $image->move(public_path('users'), $imageName);

        //delete old images
        if($user->profile_image){
            unlink(public_path($user->profile_image));
        }

        //update user
        $user->profile_image = $path;
        $user->save();
        
        return redirect()->back();
    }

    public function manageUsers(){
        $notifications = DB::table('notifications')->where('read_at',null)->get();
        $roles = Role::with(['users'=>function($query){
             $query->limit(4);
        }])->get();

        return inertia('users/ManageUsers', compact('notifications','roles'));
    }

    public function addUser(Request $request){
        $roles = Role::get();
        $departments = Department::with(['division'=>function($query){
            $query->with('unit');
        }])->get();
        $user = User::where('email', $request->email)->first();
        if(!$user){
            $user = [];
        }
        if($request->notification_id){
            $notification = DB::table('notifications')->where('id', $request->notification_id)->update(['read_at'=>now()]);
        }
        return inertia('users/AddUser', compact('roles', 'departments','user'));
    }

    public function editUser(Request $request){
        $user = User::with('roles')->find($request->id);
        $user_roles = $user->roles->pluck('name');
         $roles = Role::get();
        $departments = Department::with(['division'=>function($query){
            $query->with('unit');
        }])->get();
        $dpt = Department::find($user->department_id);
        $division = '';
        $unit = '';
        if($user->division_id){
          $division = Division::find($user->division_id);  
        }

        if($user->unit_id){
            $unit = Unit::find($user->unit_id);
        }
     
        return inertia('users/EditUser', compact('user', 'roles', 'departments', 'dpt', 'division', 'unit','user_roles'));
    }

    public function postEditUser(Request $request){
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
              'department' => 'required',
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
              'department' => 'required',
              'phone' => 'sometimes|unique:users'
          ]);
        }
          
  
          DB::transaction(function () use ($request) {
              //$code = $this->generateSixDigitCode();
  
              $user = User::find($request->id);
              $user->first_name = $request->first_name;
              $user->last_name = $request->last_name;
              $user->gender = $request->gender;
              $user->grade_level = $request->grade_level;
              $user->email = $request->email;
             
              $user->department_id = $request->department;
              $user->division_id = $request->division;
              $user->unit_id = $request->unit;
              $user->phone = $request->phone;
              $user->is_approved = $request->is_approved;
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
  
          });
  
          return redirect()->back()->with('success',true);
      }

      public function RoleUsers(Request $request){
        $role = Role::with('users')->find($request->role_id);
        return inertia('users/RoleUsers', compact('role'));
    }

    public function deleteUser($id){
        User::find($id)->delete();
        return redirect()->back();
    }

}
