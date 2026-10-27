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
use Inertia\Inertia;

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

        if ($user->hasRole('assistant director')) {
            $users = User::select('id', 'first_name', 'last_name', 'email', 'profile_image', 'is_on_leave', 'is_on_official_assignment')
                ->where('unit_id', $user->unit_id)
                ->where('id', '!=', $user->id)
                ->where(function ($q) use ($search) {
                    $q->where('first_name', 'LIKE', "%{$search}%")
                    ->orWhere('last_name', 'LIKE', "%{$search}%");
                })
                ->withCount(['taskAssignments as active_tasks_count' => function($q) {
                    $q->whereNotIn('status', ['completed', 'returned']);
                }])
                ->get();

            return response()->json(['data' => $users, 'type' => 'users']);
        }

        if($user->hasRole('deputy director')){ 
            $units = Unit::with(['users' => function($query){
                $query->role('assistant director');
            }])
            ->where('division_id', auth()->user()->division_id)
            ->where('title', 'LIKE', '%'.$search.'%')
            ->get();
            
            // Map 'users' to 'user' for frontend compatibility
            $units->transform(function($unit) {
                $unit->user = $unit->users;
                return $unit;
            });
            
            return response()->json(['data'=>$units, 'type'=>'unit']);
        }

        if($user->hasAnyRole(['super user', 'Accountant-General'])){
            $trimmedSearch = trim($search ?? '');
            $cleanSearch = trim(preg_replace('/\b(director|deputy)\b/i', '', $trimmedSearch));

            $departments = Department::with(['user' => function($query){
                $query->whereHas('roles', function($rq){
                    $rq->whereIn('name', ['director', 'deputy director']);
                });
            }])
            ->where(function($q) use ($trimmedSearch, $cleanSearch) {
                if (!empty($trimmedSearch)) {
                    $q->where('title', 'LIKE', '%'.$trimmedSearch.'%');
                    if (!empty($cleanSearch)) {
                        $q->orWhere('title', 'LIKE', '%'.$cleanSearch.'%');
                    } elseif (stripos($trimmedSearch, 'director') !== false) {
                        $q->orWhereHas('user', function($uq) {
                            $uq->whereHas('roles', function($rq){
                                $rq->whereIn('name', ['director', 'deputy director']);
                            });
                        });
                    }
                    $q->orWhereHas('user', function($uq) use ($trimmedSearch, $cleanSearch) {
                        $uq->whereHas('roles', function($rq){
                            $rq->whereIn('name', ['director', 'deputy director']);
                        })->where(function($sub) use ($trimmedSearch, $cleanSearch) {
                            $sub->where('first_name', 'LIKE', "%{$trimmedSearch}%")
                                ->orWhere('last_name', 'LIKE', "%{$trimmedSearch}%");
                            if (!empty($cleanSearch)) {
                                $sub->orWhere('first_name', 'LIKE', "%{$cleanSearch}%")
                                    ->orWhere('last_name', 'LIKE', "%{$cleanSearch}%");
                            }
                        });
                    });
                }
            })
            ->when($user->department_id, function($q) use ($user) {
                $q->where('id', '!=', $user->department_id);
            })
            ->get();

            $departments->transform(function($dept) {
                $directors = $dept->user ? $dept->user->filter(function($u){ return $u->hasRole('director'); }) : collect();
                $deputies = $dept->user ? $dept->user->filter(function($u){ return $u->hasRole('deputy director'); }) : collect();

                if ($directors->count() > 0) {
                    $dept->users = $directors->values();
                    $dept->user = $directors->values();
                    $director = $directors->first();
                    $dept->display_title = "Director, {$dept->title} ({$director->first_name} {$director->last_name})";
                } elseif ($deputies->count() > 0) {
                    $dept->users = $deputies->values();
                    $dept->user = $deputies->values();
                    $deputy = $deputies->first();
                    $dept->display_title = "Deputy Director, {$dept->title} ({$deputy->first_name} {$deputy->last_name})";
                } else {
                    $dept->users = collect();
                    $dept->user = collect();
                    $dept->display_title = "{$dept->title} (No Director Assigned)";
                }
                return $dept;
            });

            $divisions = collect();
            if($user->hasRole('director')) {
                $divisions = Division::with(['users' => function($query){
                    $query->role('deputy director');
                }]) 
                ->where('department_id', auth()->user()->department_id)
                ->where('title', 'LIKE', '%'.$search.'%')
                ->get();

                // Map 'users' to 'user' for frontend compatibility
                $divisions->transform(function($div) {
                    $div->user = $div->users;
                    return $div;
                });
            }
            
            // Allow AGF to search for individual staff too, only if search is not empty
            $staff = collect();
            if (!empty(trim($search))) {
                $staff = User::select('id', 'first_name', 'last_name', 'email', 'profile_image')
                    ->where(function ($q) use ($search) {
                        $q->where('first_name', 'LIKE', "%{$search}%")
                        ->orWhere('last_name', 'LIKE', "%{$search}%");
                    })
                    ->withCount(['taskAssignments as active_tasks_count' => function($q) {
                        $q->whereNotIn('status', ['completed', 'returned']);
                    }])
                    ->get();
            }
           
            return response()->json(['data'=>$departments->concat($divisions)->concat($staff), 'type'=>'department']);
        }

        if($user->hasRole('director')){
            $divisions = Division::with(['users' => function($query){
                $query->role('deputy director');
            }]) 
            ->where('department_id', auth()->user()->department_id)
            ->where('title', 'LIKE', '%'.$search.'%')
            ->get();
            
            // Map 'users' to 'user' for frontend compatibility
            $divisions->transform(function($div) {
                $div->user = $div->users;
                return $div;
            });
            
            return response()->json(['data'=>$divisions, 'type'=>'division']);
        }

          if ($user->hasRole('super user')) {
              $departments = \App\Models\Department::withWhereHas('user', function($query){
                  $query->role('director');
              }) 
              ->where('title', 'LIKE', '%'.$search.'%')
              ->get();
              return response()->json(['data'=>$departments, 'type'=>'department']);
          }

          if ($user->hasAnyRole(['super admin', 'admin'])) {
                $users = User::select('id', 'first_name', 'last_name', 'email', 'profile_image')
                    ->where(function ($q) use ($search) {
                        $q->where('first_name', 'LIKE', "%{$search}%")
                        ->orWhere('last_name', 'LIKE', "%{$search}%");
                    })
                    ->withCount(['taskAssignments as active_tasks_count' => function($q) {
                        $q->whereNotIn('status', ['completed', 'returned']);
                    }])
                    ->get();

                return response()->json(['data' => $users, 'type' => 'users']);
            }

        
    }

    public function updateProfileImage(Request $request){
        $request->validate([
            'file' => 'nullable|image|mimes:jpeg,png,jpg,gif,tiff,bmp,webp',
            'signature_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,tiff,bmp,webp',
            'approval_pin' => 'nullable|string|min:4|max:10',
        ]);

        $user = User::find(auth()->user()->id);
        
        // Profile Image
        if($request->file('file')){
             $image = $request->file('file');
            $imageName = time() . '.' . $image->extension();
            $path = 'users'.'/'.$imageName;
            $image->move(public_path('users'), $imageName);

            //delete old images
            if($user->profile_image && file_exists(public_path($user->profile_image))){
                unlink(public_path($user->profile_image));
            }

            //update user
            $user->profile_image = $path;
        }

        // Digital Signature
        if($request->file('signature_file')){
            $signature = $request->file('signature_file');
            $signatureName = time() . '_sig.' . $signature->extension();
            $path = 'signatures'.'/'.$signatureName;
            
            // create directory if it doesn't exist
            if (!file_exists(public_path('signatures'))) {
                mkdir(public_path('signatures'), 0755, true);
            }
            
            $signature->move(public_path('signatures'), $signatureName);

            //delete old signature
            if($user->digital_signature_path && file_exists(public_path($user->digital_signature_path))){
                unlink(public_path($user->digital_signature_path));
            }

            //update user
            $user->digital_signature_path = $path;
        }

        // PIN
        if($request->filled('approval_pin')){
            $user->approval_pin = $request->approval_pin;
        }
       
        if($request->email_password){
            $user->email_password = encrypt($request->email_password);
        }
        
        $user->save();
        
        return redirect()->back();
    }

    public function manageUsers()
    {
        // 1. Fetch unapproved account requests (for AccountRequestModal)
        $unapprovedUsers = User::where('is_approved', 0)->whereNull('deleted_at')->latest()->get();
        $notifications = [];

        foreach ($unapprovedUsers as $u) {
            $notif = DB::table('notifications')
                ->where('notifiable_id', $u->id)
                ->where('type', Registration::class)
                ->first();

            if ($notif) {
                $data = json_decode($notif->data, true) ?: [];
                $data['id'] = $u->id;
                $data['first_name'] = $u->first_name;
                $data['last_name'] = $u->last_name;
                $data['email'] = $u->email;
                $data['grade_level'] = (string)($u->grade_level ?? 0);
                $data['phone'] = $u->phone;
                $data['gender'] = $u->gender;
                $notif->data = json_encode($data);
                $notifications[] = $notif;
            } else {
                $notifications[] = (object)[
                    'id' => 'req_' . $u->id,
                    'type' => Registration::class,
                    'notifiable_type' => User::class,
                    'notifiable_id' => $u->id,
                    'data' => json_encode([
                        'id' => $u->id,
                        'first_name' => $u->first_name,
                        'last_name' => $u->last_name,
                        'email' => $u->email,
                        'grade_level' => (string)($u->grade_level ?? 0),
                        'phone' => $u->phone,
                        'gender' => $u->gender,
                    ]),
                    'read_at' => null,
                    'created_at' => $u->created_at,
                    'updated_at' => $u->updated_at,
                ];
            }
        }

        // 2. Spatie roles hierarchy ordering
        $roleOrder = [
            'super admin' => 1,
            'admin' => 2,
            'Accountant-General' => 3,
            'Chief of Staff' => 4,
            'Special Adviser / Technical Adviser' => 5,
            'director' => 6,
            'deputy director' => 7,
            'assistant director' => 8,
            'super user' => 9,
            'record manager' => 10,
            'registry' => 11,
            'AGF Registry' => 11,
            'secretary' => 12,
            'regular user' => 13
        ];

        // 3. Query all approved users with full structural hierarchy & active desk designation
        $users = User::with([
            'roles:id,name',
            'department:id,title',
            'division:id,title',
            'unit:id,title',
            'section:id,title',
            'designation:id,title,hierarchy_level,is_head_of_office,is_registry,cadre,grade_level'
        ])
        ->where('is_approved', 1)
        ->whereNull('deleted_at')
        ->latest('updated_at')
        ->get()
        ->map(function ($u) use ($roleOrder) {
            $userRoleNames = $u->roles->pluck('name')->toArray();
            $primaryRole = 'regular user';
            $minSort = 99;

            foreach ($userRoleNames as $rn) {
                $sortVal = $roleOrder[$rn] ?? 50;
                if ($sortVal < $minSort) {
                    $minSort = $sortVal;
                    $primaryRole = $rn;
                }
            }
            if (empty($userRoleNames)) {
                $primaryRole = 'regular user';
                $minSort = 99;
            }

            return [
                'id' => $u->id,
                'first_name' => $u->first_name,
                'last_name' => $u->last_name,
                'email' => $u->email,
                'phone' => $u->phone,
                'gender' => $u->gender,
                'ippis_no' => $u->ippis_no,
                'grade_level' => $u->grade_level,
                'step' => $u->step,
                'profile_image' => $u->profile_image,
                'is_approved' => $u->is_approved,
                'is_online' => $u->is_online,
                'is_on_leave' => (bool)$u->is_on_leave,
                'is_two_factor_enabled' => (bool)($u->two_factor_confirmed_at || $u->is_two_factor_enabled),
                'has_signature' => !empty($u->digital_signature_path),
                'has_pin' => !empty($u->approval_pin),
                'is_department_head' => (bool)$u->is_department_head,
                'is_division_head' => (bool)$u->is_division_head,
                'is_unit_head' => (bool)$u->is_unit_head,
                'is_section_head' => (bool)$u->is_section_head,
                'created_at' => $u->created_at,
                'updated_at' => $u->updated_at,
                'department' => $u->department ? ['id' => $u->department->id, 'title' => $u->department->title] : null,
                'division' => $u->division ? ['id' => $u->division->id, 'title' => $u->division->title] : null,
                'unit' => $u->unit ? ['id' => $u->unit->id, 'title' => $u->unit->title] : null,
                'section' => $u->section ? ['id' => $u->section->id, 'title' => $u->section->title] : null,
                'designation' => $u->designation ? [
                    'id' => $u->designation->id,
                    'title' => $u->designation->title,
                    'hierarchy_level' => $u->designation->hierarchy_level,
                    'is_head_of_office' => (bool)$u->designation->is_head_of_office,
                    'is_registry' => (bool)$u->designation->is_registry,
                    'cadre' => $u->designation->cadre,
                    'grade_level' => $u->designation->grade_level,
                ] : null,
                'roles' => $u->roles->map(fn($r) => ['id' => $r->id, 'name' => $r->name]),
                'role_names' => $userRoleNames,
                'primary_role' => $primaryRole,
                'role_sort' => $minSort,
            ];
        });

        // 4. Spatie roles catalog with live user counts
        $roles = Role::all()->map(function ($r) use ($roleOrder, $users) {
            $count = $users->filter(fn($u) => in_array($r->name, $u['role_names']))->count();
            return [
                'id' => $r->id,
                'name' => $r->name,
                'user_count' => $count,
                'sort_order' => $roleOrder[$r->name] ?? 99,
            ];
        })->sortBy('sort_order')->values();

        // 4. Executive Telemetry Stats
        $stats = [
            'total_approved' => $users->count(),
            'pending_requests' => count($notifications),
            'heads_of_office' => $users->filter(fn($u) => $u['is_department_head'] || $u['is_division_head'] || $u['is_unit_head'] || $u['is_section_head'] || ($u['designation']['is_head_of_office'] ?? false))->count(),
            'on_leave' => $users->filter(fn($u) => $u['is_on_leave'])->count(),
            'with_signature' => $users->filter(fn($u) => $u['has_signature'])->count(),
            'with_pin' => $users->filter(fn($u) => $u['has_pin'])->count(),
            'with_2fa' => $users->filter(fn($u) => $u['is_two_factor_enabled'])->count(),
        ];

        // 5. Structure reference data for cascading filters
        $departments = Department::select('id', 'title')
            ->with(['division' => function($q) {
                $q->select('id', 'department_id', 'title')->with(['unit' => function($uq) {
                    $uq->select('id', 'division_id', 'title')->with('sections:id,unit_id,title');
                }]);
            }])
            ->orderBy('title')
            ->get();

        return inertia('users/ManageUsers', [
            'users' => $users,
            'roles' => $roles,
            'notifications' => $notifications,
            'stats' => $stats,
            'departments' => $departments,
        ]);
    }

    public function addUser(Request $request){
        $roles = Role::with('permissions')->get()->map(function($r) {
            return [
                'id' => $r->id,
                'name' => $r->name,
                'description' => $r->description ?? '',
                'permissions' => $r->permissions->pluck('name')->toArray(),
            ];
        });
        $departments = Department::with(['division'=>function($query){
            $query->with(['unit' => function($uq) {
                $uq->with('sections');
            }]);
        }])->get();
        $designations = \App\Models\Designation::active()->get();
        $user = User::where('email', $request->email)->first();
        if(!$user){
            $user = [];
        }
        $all_permissions = \App\Services\PermissionRegistryService::getGroupedPermissions();
        // Do not mark notification as read here. Mark it when actually approved.
        return inertia('users/AddUser', compact('roles', 'departments','user', 'designations', 'all_permissions'));
    }

    public function editUser(Request $request){
        $user = User::with(['roles.permissions', 'designation', 'section'])->find($request->id);
        if (!$user) {
            return redirect('/dashboard/manage-users')->with('error', 'Officer record not found.');
        }
        $user_roles = $user->roles->pluck('name');
        $direct_permissions = $user->getDirectPermissions()->pluck('name')->toArray();
        $role_permissions = $user->getPermissionsViaRoles()->pluck('name')->toArray();
        $user_permissions = $user->getAllPermissions()->pluck('name')->toArray();
        $all_roles = Role::with('permissions')->get()->map(function($r) {
            return [
                'id' => $r->id,
                'name' => $r->name,
                'description' => $r->description ?? '',
                'permissions' => $r->permissions->pluck('name')->toArray(),
            ];
        });
        $departments = Department::with(['division'=>function($query){
            $query->with(['unit' => function($uq) {
                $uq->with('sections');
            }]);
        }])->get();
        $designations = \App\Models\Designation::active()->get();
        $dpt = Department::find($user->department_id);
        $division = '';
        $unit = '';
        $section = null;
        if($user->division_id){
          $division = Division::find($user->division_id);  
        }

        if($user->unit_id){
            $unit = Unit::find($user->unit_id);
        }

        if($user->section_id){
            $section = \App\Models\Section::find($user->section_id);
        }

        $all_permissions = \App\Services\PermissionRegistryService::getGroupedPermissions();
     
        return inertia('users/EditUser', [
            'officer' => $user,
            'officer_roles' => $user_roles,
            'all_roles' => $all_roles,
            'departments' => $departments,
            'dpt' => $dpt,
            'division' => $division,
            'unit' => $unit,
            'section' => $section,
            'designations' => $designations,
            'user_permissions' => $user_permissions,
            'direct_permissions' => $direct_permissions,
            'role_permissions' => $role_permissions,
            'all_permissions' => $all_permissions,
        ]);
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
              $user->designation_id = $request->designation_id;
              $user->email = $request->email;
             
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
  
              \App\Services\AuditTrailService::logPrivilege(
                  'officer_profile_updated',
                  "Officer profile updated: {$user->first_name} {$user->last_name} (GL {$user->grade_level})",
                  [
                      'target_user_id' => $user->id,
                      'target_email' => $user->email,
                      'assigned_roles' => $request->role ?? [],
                      'direct_permissions' => $directOverrides ?? [],
                      'department_id' => $user->department_id,
                      'designation_id' => $user->designation_id,
                  ],
                  $user,
                  \App\Services\AuditTrailService::SEVERITY_NOTICE
              );
          });
  
          return redirect()->back()->with('success',true);
      }

      public function RoleUsers(Request $request){
        $role = Role::with('users')->find($request->role_id);
        return inertia('users/RoleUsers', compact('role'));
    }

    public function deleteUser($id){
        $user = User::find($id);
        if ($user) {
            \App\Services\AuditTrailService::logPrivilege(
                'officer_deleted',
                "Officer record removed: {$user->first_name} {$user->last_name} ({$user->email})",
                ['target_user_id' => $user->id, 'email' => $user->email],
                $user,
                \App\Services\AuditTrailService::SEVERITY_WARNING
            );
            $user->delete();
        }
        return redirect()->back();
    }

    public function rejectAccountRequest(Request $request){
        $id = $request->id;
        $userId = is_string($id) && str_starts_with($id, 'req_') ? (int)substr($id, 4) : (int)$id;

        $user = User::where('id', $userId)->where('is_approved', 0)->first();
        if ($user) {
            DB::table('notifications')
                ->where('notifiable_id', $user->id)
                ->where('type', Registration::class)
                ->delete();
            $user->forceDelete();
        } else {
            DB::table('notifications')
                ->where('id', $id)
                ->delete();
        }
        return redirect()->back()->with('success', 'Account request has been declined.');
    }

    public function toggleUserStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:users,id',
            'is_approved' => 'required|boolean',
        ]);

        $user = User::findOrFail($request->id);
        
        // Prevent deactivating oneself
        if (auth()->id() == $user->id && !$request->is_approved) {
            return redirect()->back()->withErrors(['error' => 'You cannot deactivate your own account.']);
        }

        $user->is_approved = $request->is_approved ? 1 : 0;
        $user->save();

        \App\Services\AuditTrailService::logPrivilege(
            $user->is_approved ? 'officer_reactivated' : 'officer_suspended',
            "Officer account " . ($user->is_approved ? 'reactivated' : 'suspended') . ": {$user->first_name} {$user->last_name}",
            ['target_user_id' => $user->id, 'is_approved' => $user->is_approved],
            $user,
            \App\Services\AuditTrailService::SEVERITY_WARNING
        );

        return redirect()->back()->with([
            'success' => true,
            'message' => 'Officer account status updated successfully.'
        ]);
    }

    public function resetApprovalPin(Request $request)
    {
        $request->validate(['id' => 'required|exists:users,id']);
        $user = User::findOrFail($request->id);
        $user->approval_pin = null;
        $user->save();

        \App\Services\AuditTrailService::logSecurity(
            'approval_pin_reset',
            "Officer approval PIN reset by administrative authority for {$user->first_name} {$user->last_name}",
            ['target_user_id' => $user->id],
            $user,
            \App\Services\AuditTrailService::SEVERITY_WARNING
        );

        return redirect()->back()->with([
            'success' => true,
            'message' => 'Approval PIN reset successfully for ' . $user->first_name . ' ' . $user->last_name . '.'
        ]);
    }

    public function resetDigitalSignature(Request $request)
    {
        $request->validate(['id' => 'required|exists:users,id']);
        $user = User::findOrFail($request->id);
        if ($user->digital_signature_path && file_exists(public_path($user->digital_signature_path))) {
            @unlink(public_path($user->digital_signature_path));
        }
        $user->digital_signature_path = null;
        $user->save();

        \App\Services\AuditTrailService::logSecurity(
            'digital_signature_cleared',
            "Digital signature cleared by administrative authority for {$user->first_name} {$user->last_name}",
            ['target_user_id' => $user->id],
            $user,
            \App\Services\AuditTrailService::SEVERITY_WARNING
        );

        return redirect()->back()->with([
            'success' => true,
            'message' => 'Digital signature cleared successfully for ' . $user->first_name . ' ' . $user->last_name . '.'
        ]);
    }

    public function sendPasswordReset(Request $request)
    {
        $request->validate(['id' => 'required|exists:users,id']);
        $user = User::findOrFail($request->id);

        \Illuminate\Support\Facades\Password::sendResetLink(['email' => $user->email]);

        \App\Services\AuditTrailService::logSecurity(
            'password_reset_initiated',
            "Administrative password reset link dispatched to {$user->email}",
            ['target_user_id' => $user->id, 'email' => $user->email],
            $user,
            \App\Services\AuditTrailService::SEVERITY_NOTICE
        );

        return redirect()->back()->with([
            'success' => true,
            'message' => 'Password reset dispatch initiated for ' . $user->email . '.'
        ]);
    }

    public function exportUsersCsv(Request $request)
    {
        $query = User::with([
            'roles:id,name',
            'department:id,title',
            'division:id,title',
            'unit:id,title',
            'section:id,title',
            'designation:id,title,grade_level,cadre,is_head_of_office,is_registry'
        ])->where('is_approved', 1)->whereNull('deleted_at');

        if ($request->filled('role')) {
            $query->whereHas('roles', fn($q) => $q->where('name', $request->role));
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('ippis_no', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        $users = $query->orderBy('last_name')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="civil_service_officers_' . date('Y-m-d_His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'IPPIS No',
                'Last Name',
                'First Name',
                'Email',
                'Phone',
                'Gender',
                'Grade Level',
                'Step',
                'Department',
                'Division',
                'Unit',
                'Section',
                'Functional Desk / Designation',
                'Cadre',
                'Assigned Roles',
                'Head of Office',
                'Leave Status',
                'Digital Signature Set',
                'Approval PIN Set',
                '2FA Enabled',
                'Onboarding Date'
            ]);

            foreach ($users as $u) {
                fputcsv($file, [
                    $u->ippis_no ?? 'N/A',
                    $u->last_name,
                    $u->first_name,
                    $u->email,
                    $u->phone ?? 'N/A',
                    ucfirst($u->gender ?? 'N/A'),
                    $u->grade_level ?? 'N/A',
                    $u->step ?? 'N/A',
                    $u->department?->title ?? 'None',
                    $u->division?->title ?? 'None',
                    $u->unit?->title ?? 'None',
                    $u->section?->title ?? 'None',
                    $u->designation?->title ?? 'Unassigned Desk',
                    $u->designation?->cadre ?? 'General',
                    $u->roles->pluck('name')->implode(', '),
                    ($u->is_department_head || $u->is_division_head || $u->is_unit_head || $u->is_section_head || $u->designation?->is_head_of_office) ? 'Yes' : 'No',
                    $u->is_on_leave ? 'On Leave' : 'Active Duty',
                    !empty($u->digital_signature_path) ? 'Yes' : 'No',
                    !empty($u->approval_pin) ? 'Yes' : 'No',
                    ($u->two_factor_confirmed_at || $u->is_two_factor_enabled) ? 'Yes' : 'No',
                    $u->created_at ? $u->created_at->format('Y-m-d H:i') : 'N/A',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function getOfficerDossier($id)
    {
        $user = User::with([
            'roles.permissions',
            'department:id,title',
            'division:id,title',
            'unit:id,title',
            'section:id,title',
            'designation:id,title,hierarchy_level,is_head_of_office,is_registry,cadre,grade_level'
        ])
        ->withCount([
            'taskAssignments as active_tasks_count' => function($q) {
                $q->whereNotIn('status', ['completed', 'returned']);
            },
            'taskAssignments as completed_tasks_count' => function($q) {
                $q->where('status', 'completed');
            }
        ])
        ->findOrFail($id);

        $directPermissions = $user->getDirectPermissions()->pluck('name')->toArray();
        $rolePermissions = $user->getPermissionsViaRoles()->pluck('name')->toArray();
        $allPermissions = $user->getAllPermissions()->pluck('name')->toArray();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'gender' => $user->gender,
                'ippis_no' => $user->ippis_no,
                'grade_level' => $user->grade_level,
                'step' => $user->step,
                'profile_image' => $user->profile_image,
                'is_approved' => $user->is_approved,
                'is_online' => $user->is_online,
                'is_on_leave' => (bool)$user->is_on_leave,
                'is_two_factor_enabled' => (bool)($user->two_factor_confirmed_at || $user->is_two_factor_enabled),
                'has_signature' => !empty($user->digital_signature_path),
                'digital_signature_path' => $user->digital_signature_path,
                'has_pin' => !empty($user->approval_pin),
                'is_department_head' => (bool)$user->is_department_head,
                'is_division_head' => (bool)$user->is_division_head,
                'is_unit_head' => (bool)$user->is_unit_head,
                'is_section_head' => (bool)$user->is_section_head,
                'date_of_birth' => $user->date_of_birth,
                'date_of_first_appointment' => $user->date_of_first_appointment,
                'date_of_confirmation' => $user->date_of_confirmation,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'department' => $user->department,
                'division' => $user->division,
                'unit' => $user->unit,
                'section' => $user->section,
                'designation' => $user->designation,
                'roles' => $user->roles->map(fn($r) => ['id' => $r->id, 'name' => $r->name]),
                'active_tasks_count' => $user->active_tasks_count,
                'completed_tasks_count' => $user->completed_tasks_count,
            ],
            'direct_permissions' => $directPermissions,
            'role_permissions' => $rolePermissions,
            'total_permissions_count' => count($allPermissions),
        ]);
    }

    public function getUserUnreadNotification(){
        $user = auth()->user();
        if (!$user) {
            return response()->json(['notifications' => [], 'all_notifications' => [], 'count' => 0]);
        }

        $allNotifications = $user->notifications()->take(60)->get();
        $count = $user->unreadNotifications()->count();

        // First, group notifications by type
        $notifications = $allNotifications->groupBy('type');

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

        return response()->json([
            'notifications' => $notifications,
            'all_notifications' => $allNotifications,
            'count' => $count
        ]);
    }

    public function addLeave(Request $request){
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'user_id' => 'required|exists:users,id',
            'leave_type' => 'nullable|string',
            'relief_user_id' => 'nullable|exists:users,id',
            'destination' => 'nullable|string',
            'duty_memo_ref' => 'nullable|string',
        ]);

        $leaveType = $request->leave_type ?: 'annual';
        $today = now()->toDateString();
        $isCurrentlyActive = ($request->start_date <= $today && $request->end_date >= $today);

        $leave = Leave::create([
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'user_id' => $request->user_id,
            'leave_type' => $leaveType,
            'status' => 'approved',
            'relief_user_id' => $request->relief_user_id,
            'destination' => $request->destination,
            'duty_memo_ref' => $request->duty_memo_ref,
            'approved_by' => auth()->id(),
        ]);

        $user = User::find($request->user_id);
        if ($user) {
            if ($leaveType === 'official_assignment') {
                $user->is_on_official_assignment = $isCurrentlyActive;
                $user->is_on_leave = false;
            } else {
                $user->is_on_leave = $isCurrentlyActive;
                $user->is_on_official_assignment = false;
            }
            $user->save();
        }

        return redirect()->back()->with('success', 'Leave / Duty assignment recorded successfully.');
    }

    public function applyLeave(Request $request){
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'leave_type' => 'required|string',
            'relief_user_id' => 'nullable|exists:users,id',
            'reason' => 'nullable|string',
            'destination' => 'nullable|string',
            'duty_memo_ref' => 'nullable|string',
        ]);

        $user = auth()->user();
        $isElevated = $user->hasAnyRole(['super user', 'super admin', 'admin', 'Accountant-General', 'director']);

        $leaveType = $request->leave_type;
        $status = $isElevated ? 'approved' : 'pending';
        $today = now()->toDateString();
        $isCurrentlyActive = ($request->start_date <= $today && $request->end_date >= $today);

        $leave = Leave::create([
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'leave_type' => $leaveType,
            'status' => $status,
            'reason' => $request->reason,
            'user_id' => $user->id,
            'relief_user_id' => $request->relief_user_id,
            'destination' => $request->destination,
            'duty_memo_ref' => $request->duty_memo_ref,
            'approved_by' => $isElevated ? $user->id : null,
        ]);

        if ($isElevated && $isCurrentlyActive) {
            if ($leaveType === 'official_assignment') {
                $user->is_on_official_assignment = true;
                $user->is_on_leave = false;
            } else {
                $user->is_on_leave = true;
                $user->is_on_official_assignment = false;
            }
            $user->save();
        }

        return redirect()->back()->with('success', $isElevated ? 'Leave / Assignment recorded.' : 'Leave request submitted for supervisor approval.');
    }

    public function approveLeave(Request $request){
        $request->validate([
            'leave_id' => 'required|exists:leaves,id',
        ]);

        $leave = Leave::findOrFail($request->leave_id);
        $leave->status = 'approved';
        $leave->approved_by = auth()->id();
        $leave->supervisor_comment = $request->comment;
        $leave->save();

        $today = now()->toDateString();
        $user = User::find($leave->user_id);
        if ($user && $leave->start_date <= $today && $leave->end_date >= $today) {
            if ($leave->leave_type === 'official_assignment') {
                $user->is_on_official_assignment = true;
                $user->is_on_leave = false;
            } else {
                $user->is_on_leave = true;
                $user->is_on_official_assignment = false;
            }
            $user->save();
        }

        return redirect()->back()->with('success', 'Leave application approved successfully.');
    }

    public function rejectLeave(Request $request){
        $request->validate([
            'leave_id' => 'required|exists:leaves,id',
            'comment' => 'required|string',
        ]);

        $leave = Leave::findOrFail($request->leave_id);
        $leave->status = 'rejected';
        $leave->approved_by = auth()->id();
        $leave->supervisor_comment = $request->comment;
        $leave->save();

        return redirect()->back()->with('success', 'Leave application rejected.');
    }

    public function cancelLeave(Request $request){
        $leave = Leave::find($request->leave_id);
        if ($leave) {
            $leave->status = 'cancelled';
            $leave->save();
            $leave->delete();
        }

        $userId = $request->user_id ?: ($leave?->user_id);
        if ($userId) {
            $user = User::find($userId);
            if ($user) {
                $user->is_on_leave = false;
                $user->is_on_official_assignment = false;
                $user->save();
            }
        }
        return redirect()->back()->with('success', 'Leave record cancelled successfully.');
    }

    public function getUserLeaves(Request $request){
        $userId = $request->user_id ?: auth()->id();
        $leaves = Leave::with(['reliefUser', 'approver'])
            ->where('user_id', $userId)
            ->latest('id')
            ->get();

        return response()->json($leaves);
    }

    public function getLeaveReliefOfficers(Request $request){
        $user = auth()->user();
        if (!$user) return response()->json(['is_agf' => false, 'officers' => []], 401);

        $targetUserId = $request->user_id ?: $user->id;
        $targetUser = User::find($targetUserId) ?: $user;

        $isAgf = $targetUser->hasAnyRole(['Accountant-General', 'agf', 'super user']);

        if ($isAgf) {
            // AGF can hand over to any of the Directors
            $directors = User::role('director')
                ->with('department')
                ->where('id', '!=', $targetUser->id)
                ->orderBy('first_name')
                ->get()
                ->map(function($d) {
                    $deptTitle = $d->department ? ($d->department->title ?: $d->department->name) : 'Directorate';
                    return [
                        'id' => $d->id,
                        'first_name' => $d->first_name,
                        'last_name' => $d->last_name,
                        'email' => $d->email,
                        'role_title' => 'Director',
                        'department' => $deptTitle,
                        'display_label' => "Director - {$d->first_name} {$d->last_name} ({$deptTitle})"
                    ];
                });

            return response()->json([
                'is_agf' => true,
                'officers' => $directors
            ]);
        }

        // For other staff: return departmental colleagues
        $query = User::select('id', 'first_name', 'last_name', 'email', 'department_id', 'division_id', 'unit_id')
            ->with('department')
            ->where('id', '!=', $targetUser->id);

        if ($targetUser->department_id) {
            $query->where('department_id', $targetUser->department_id);
        }

        $colleagues = $query->orderBy('first_name')->get()->map(function($c) {
            $deptTitle = $c->department ? ($c->department->title ?: $c->department->name) : 'HQ';
            return [
                'id' => $c->id,
                'first_name' => $c->first_name,
                'last_name' => $c->last_name,
                'email' => $c->email,
                'role_title' => 'Colleague',
                'department' => $deptTitle,
                'display_label' => "{$c->first_name} {$c->last_name} ({$c->email})"
            ];
        });

        return response()->json([
            'is_agf' => false,
            'officers' => $colleagues
        ]);
    }

    public function getPendingLeaves(Request $request){
        $user = auth()->user();
        if (!$user) return response()->json([], 401);

        $query = Leave::with(['user.department', 'user.division', 'user.unit', 'reliefUser'])
            ->where('status', 'pending');

        $isPrivileged = $user->hasAnyRole(['super user', 'super admin', 'admin', 'Accountant-General', 'director']);

        if (!$isPrivileged) {
            // Check if head of department/division/unit
            $deptId = $user->department_id;
            $divId = $user->division_id;
            $unitId = $user->unit_id;

            $query->whereHas('user', function($q) use ($deptId, $divId, $unitId) {
                if ($unitId) {
                    $q->where('unit_id', $unitId);
                } elseif ($divId) {
                    $q->where('division_id', $divId);
                } elseif ($deptId) {
                    $q->where('department_id', $deptId);
                }
            });
        }

        $pending = $query->latest('id')->get();
        return response()->json($pending);
    }

    public function misConduct(Request $request){
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'ticket' => 'required|string',
            'complaint' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        $storedPath = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
            $destination = public_path('misconducts');
            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }
            $file->move($destination, $fileName);
            $storedPath = '/misconducts/' . $fileName;
        }

        $misconduct = Misconduct::create([
            'user_id' => $request->user_id,
            'reported_by' => auth()->id(),
            'ticket' => $request->ticket,
            'complaint' => $request->complaint,
            'file' => $storedPath,
            'status' => 'submitted',
            'incident_date' => now()->toDateString(),
        ]);

        return redirect()->back()->with('success', 'Misconduct incident report submitted successfully.');
    }

    public function getUserMisconducts(Request $request){
        $userId = $request->user_id ?: auth()->id();
        $records = Misconduct::with(['reporter', 'user'])
            ->where('user_id', $userId)
            ->latest('id')
            ->get();

        return response()->json($records);
    }

    public function updateTaskNotification(){
        $user = auth()->user();
        if (!$user) return response()->json(['status' => 'error'], 401);
        
        $user->unreadNotifications()
            ->whereIn('type', [
                'App\Notifications\TaskChangedNotification',
                'App\Notifications\TaskNotification',
                'App\Notifications\PaymentAssignedNotification',
                'App\Notifications\WorkflowEventNotification',
                'App\Notifications\RegistryNotification',
            ])
            ->update(['read_at' => now()]);

        return response()->json(['status' => 'success', 'unread_count' => $user->unreadNotifications()->count()]);
    }

    public function updateCommentNotification(){
        $user = auth()->user();
        if (!$user) return response()->json(['status' => 'error'], 401);

        $user->unreadNotifications()
            ->where('type', 'App\Notifications\CommentNotification')
            ->update(['read_at' => now()]);

        return response()->json(['status' => 'success', 'unread_count' => $user->unreadNotifications()->count()]);
    }

    public function verifySignaturePin(Request $request)
    {
        $request->validate([
            'pin' => 'required'
        ]);

        $user = auth()->user();
        if ((string)$user->approval_pin !== (string)$request->pin) {
            return response()->json(['valid' => false, 'message' => 'Invalid digital signature PIN.'], 200); // return 200 with valid=false to avoid global 403 interceptor
        }

        if (!$user->digital_signature_path || !file_exists(public_path($user->digital_signature_path))) {
            return response()->json(['valid' => false, 'message' => 'You do not have a digital signature configured in your profile.'], 200);
        }

        return response()->json(['valid' => true, 'path' => $user->digital_signature_path]);
    }

    public function markNotificationAsRead(Request $request){
        $user = auth()->user();
        if (!$user) return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);

        // Mark individual notification by ID
        if ($request->filled('id')) {
            $notification = $user->notifications()->where('id', $request->id)->first();
            if ($notification) {
                $notification->markAsRead();
            }
            return response()->json(['status' => 'success', 'unread_count' => $user->unreadNotifications()->count()]);
        }

        // Mark category or all
        if ($request->type === 'Task') {
            return $this->updateTaskNotification();
        }
        if ($request->type === 'Comments') {
            return $this->updateCommentNotification();
        }
        if ($request->type === 'All' || $request->boolean('all')) {
            $user->unreadNotifications()->update(['read_at' => now()]);
            return response()->json(['status' => 'success', 'unread_count' => 0]);
        }

        // Default: mark all unread notifications
        $user->unreadNotifications()->update(['read_at' => now()]);
        return response()->json(['status' => 'success', 'unread_count' => 0]);
    }

    public function markNotificationAsUnread(Request $request){
        $user = auth()->user();
        if (!$user) return response()->json(['status' => 'error'], 401);

        if ($request->filled('id')) {
            $user->notifications()->where('id', $request->id)->update(['read_at' => null]);
        }
        return response()->json(['status' => 'success', 'unread_count' => $user->unreadNotifications()->count()]);
    }

    public function deleteNotification(Request $request){
        $user = auth()->user();
        if (!$user) return response()->json(['status' => 'error'], 401);

        if ($request->filled('id')) {
            $user->notifications()->where('id', $request->id)->delete();
        } elseif ($request->boolean('clear_read')) {
            $user->readNotifications()->delete();
        }
        return response()->json(['status' => 'success', 'unread_count' => $user->unreadNotifications()->count()]);
    }

    public function notificationsHub(Request $request){
        return Inertia::render('dashboard/Notifications/Index');
    }

}
