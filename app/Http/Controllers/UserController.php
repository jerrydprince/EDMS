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
use App\Notifications\Registration;
use Carbon\Carbon;
use App\Models\Leave;
use App\Models\Misconduct;

class UserController extends Controller
{
    public function getAllUsers(){
        $users = User::get();
        return response()->json($users);
    }

    public function getBranchType(){
        if(auth()->user()->unit_id){
            return 'unit';
        }
        if(auth()->user()->division_id){
            return 'division';
        }
        if(auth()->user()->department_id){
            return 'department';
        }
    }

    public function isRole($role){
        $roles = auth()->user()->getRoleNames();
        $is_role = $roles->contains(function($item)use ($role){
            //dd($item);
            return $item == $role;
        });
        if($is_role){
            return true;
        }else{
            return false;
        }
    }

    public function searchUsers(Request $request){
        //$branchType = $this->getBranchType();
        $user = auth()->user();
        $search = $request->search;

        if ($user->hasRole('unit heads')) {
            $users = User::select('id', 'first_name', 'last_name', 'email', 'profile_image')
                ->where('unit_id', $user->unit_id)
                ->where(function ($q) use ($search) {
                    $q->where('first_name', 'LIKE', "%{$search}%")
                    ->orWhere('last_name', 'LIKE', "%{$search}%");
                })
                ->get();

            return response()->json(['data' => $users, 'type' => 'users']);
        }

        if($user->hasRole('division heads')){ 
            $units = Unit::withWhereHas('users', function($query){
                $query->role('unit heads');
            })
            ->where('division_id', auth()->user()->division_id)
            ->where('title', 'LIKE', '%'.$search.'%')
            ->get();
            return response()->json(['data'=>$units, 'type'=>'unit']);
        }

        if($user->hasRole('directors')){
            $divisions = Division::withWhereHas('users', function($query){
                $query->role('division heads');
            }) 
            ->where('department_id', auth()->user()->department_id)
            ->where('title', 'LIKE', '%'.$search.'%')
            ->get();
            return response()->json(['data'=>$divisions, 'type'=>'division']);
        }

        if($user->hasRole('super users')){
            $departments = Department::withWhereHas('user', function($query){
                $query->role('directors');
            })
            ->where('title', 'LIKE', '%'.$search.'%')->get();
           
            return response()->json(['data'=>$departments, 'type'=>'department']);
        }

          if ($user->hasAnyRole(['super admin', 'admin'])) {
                $users = User::select('id', 'first_name', 'last_name', 'email', 'profile_image')
                    ->where(function ($q) use ($search) {
                        $q->where('first_name', 'LIKE', "%{$search}%")
                        ->orWhere('last_name', 'LIKE', "%{$search}%");
                    })
                    ->get();

                return response()->json(['data' => $users, 'type' => 'users']);
            }

        
    }

    public function updateProfileImage(Request $request){
        $request->validate([
            'file' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ]);

        $user = User::find(auth()->user()->id);
        if($request->file('file')){
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
        }
       
        if($request->email_password){
            $user->email_password = encrypt($request->email_password);
        }
        $user->save();
        
        return redirect()->back();
    }

    public function manageUsers(){
        $notifications = DB::table('notifications')->where('type', Registration::class)->where('read_at',null)->get();
        $roles = Role::with(['users'])->get()->map(function($item){
           $item->users = $item->users->take(4);
           return $item;
        });

        return inertia('users/ManageUsers', compact('notifications','roles'));
    }
    // public function manageUsers()
    // {
    //     $notifications = DB::table('notifications')
    //         ->where('type', Registration::class)
    //         ->whereNull('read_at')
    //         ->get();

    //     $roles = Role::where('guard_name', 'web')
    //         ->with(['users' => fn ($q) => $q->take(4)])
    //         ->get();

    //     return inertia('users/ManageUsers', compact('notifications', 'roles'));
    // }


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
         $all_roles = Role::get();
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
     
        return inertia('users/EditUser', compact('user', 'all_roles', 'departments', 'dpt', 'division', 'unit','user_roles'));
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

    public function getUserUnreadNotification(){
        $user = auth()->user();
        $notifications = $user->notifications->take(50)->groupBy('type'); // First, group notifications by type

        $count = $notifications->flatten()->whereNull('read_at')->count(); // Flatten the collection for counting unread notifications

        // Group each type of notification by date
        $notifications = $notifications->map(function ($typeGroup) {
            return $typeGroup->groupBy(function ($notification) {
                $createdAt = Carbon::parse($notification->created_at);

                if ($createdAt->isToday()) {
                    return 'Today';
                } elseif ($createdAt->isYesterday()) {
                    return 'Yesterday';
                } elseif ($createdAt->diffInDays(Carbon::now()) <= 7) {
                    return $createdAt->diffForHumans(null, true) . ' ago'; // e.g., "2 days ago"
                } else {
                    return $createdAt->format('d F Y'); // e.g., "05 September 2024"
                }
            });
        });
        return response()->json(['notifications'=>$notifications, 'count'=>$count]);
    }

    public function addLeave(Request $request){
        $request->validate([
            'start_date' => 'required',
            'end_date' => 'required',
        ]);

        Leave::create($request->all());

        $user = User::find($request->user_id);
        $user->is_on_leave = true;
        $user->save(); 

        return redirect()->back();
    }

    public function misConduct(Request $request){
        $path = null;
        $request->validate([
           // 'ticket' => 'required'
        ]);

        if($request->file){
            $request->validate([
                'file' => 'required',
            ]);
    
            $image = $request->file('file');
            $imageName = time() . '.' . $image->extension();
            $path = 'misconduct'.'/'.$imageName;
            $image->move(public_path('misconduct'), $imageName);
        }

        $mis = new Misconduct();
        $mis->ticket = 'ticket';
        $mis->complaint = $request->ticket;
        $mis->file = $path;
        $mis->user_id = $request->user_id;
        $mis->save();
    }

    public function cancelLeave(Request $request){
        $leave = Leave::find($request->leave_id);
        $leave->delete();

        $user = User::find($request->user_id);
        $user->is_on_leave = false;
        $user->save();
        return redirect()->back();
    }

    public function updateTaskNotification(){
        $user = auth()->user();
        $notifications = $user->unreadNotifications()->where('type', 'App\Notifications\TaskChangedNotification')->get();
        foreach($notifications as $notification){
            $notification->markAsRead();
        }
        return response()->json(['status'=>'success']);
    }

    public function updateCommentNotification(){
        $user = auth()->user();
        $notifications = $user->unreadNotifications()->where('type', 'App\Notifications\CommentNotification')->get();
        foreach($notifications as $notification){
            $notification->markAsRead();
        }
        return response()->json(['status'=>'success']);
    }

    public function markNotificationAsRead(Request $request){
        if($request->type == 'Task'){
            return $this->updateTaskNotification();
        }
        if($request->type == 'Comments'){
            return $this->updateCommentNotification();
        }
    }

}
