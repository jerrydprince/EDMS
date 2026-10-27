<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function createRoles(){
        $roles = [
            [
                'name' => 'super admin', 
                'guard_name' => 'web',
                'description' => 'This access level holds unparalleled authority, overseeing the entire system without restrictions',
                'created_at'=>\Carbon\Carbon::now(), 
                'updated_at'=>\Carbon\Carbon::now(),
            ], 
            [
                'name' => 'admin', 
                'guard_name' => 'web',
                'description' => 'Focuses on document management. Configures workflows, archiving policies, and document-specific settings', 
                'created_at'=>\Carbon\Carbon::now(), 
                'updated_at'=>\Carbon\Carbon::now()
            ], 
            [
                'name' => 'director', 
                'guard_name' => 'web', 
                'description' => 'Manages departmental functions, shares files/documents with other directors, accesses department files',
                'created_at'=>\Carbon\Carbon::now(), 
                'updated_at'=>\Carbon\Carbon::now()
            ], 
            [
                'name' => 'deputy director', 
                'guard_name' => 'web', 
                'description'=> "Manages a specific division's activities, accesses division files and shares documents with the director and other division heads",
                'created_at'=>\Carbon\Carbon::now(), 
                'updated_at'=>\Carbon\Carbon::now()
            ], 
            [
                'name' => 'assistant director', 
                'guard_name' => 'web', 
                'description' => 'Manages activities of a specific unit within a division, accesses unit files and shares documents with the division head and other unit heads',
                'created_at'=>\Carbon\Carbon::now(), 
                'updated_at'=>\Carbon\Carbon::now()
            ],  
            [
                'name' => 'record manager', 
                'guard_name' => 'web', 
                'description' => "Specialized in records management, ensuring compliance with government regulations",
                'created_at'=>\Carbon\Carbon::now(), 
                'updated_at'=>\Carbon\Carbon::now()
            ], 
            [
                'name' => 'super user', 
                'guard_name' => 'web', 
                'description' => 'Manages cross-department activities, oversees all files/documents, delegates tasks, tracks file activities',
                'created_at'=>\Carbon\Carbon::now(), 
                'updated_at'=>\Carbon\Carbon::now()
            ], 
            [
                'name' => 'regular user', 
                'guard_name' => 'web', 
                'description' => 'Manages cross-department activities, oversees all files/documents, delegates tasks, tracks file activities',
                'created_at'=>\Carbon\Carbon::now(), 
                'updated_at'=>\Carbon\Carbon::now()
            ], 
            [
                'name' => 'record manager', 
                'guard_name' => 'web', 
                'description' => 'Limited access for entering data into the system, including tasks like scanning and indexing documents',
                'created_at'=>\Carbon\Carbon::now(), 
                'updated_at'=>\Carbon\Carbon::now()
            ],
        ];
        Role::insert($roles);
        echo "successful";
    }

    public function createRole(Request $request){
        Role::create([
            'name' => $request->name, 
                'guard_name' => 'web',
                'role_type' => $request->type,
                'description' => $request->description,
                'created_at'=>\Carbon\Carbon::now(), 
                'updated_at'=>\Carbon\Carbon::now(),
        ]);

        return redirect()->back();
    }

    public function createCustomPermission(Request $request){
        $validated = $request->validate([
            'module' => 'required|string|max:100',
            'action' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
        ]);

        $module = preg_replace('/\s+/', ' ', trim($validated['module']));
        $action = preg_replace('/\s+/', ' ', trim($validated['action']));
        $permName = strtolower($module . ' ' . $action);

        $existing = Permission::where('name', $permName)->where('guard_name', 'web')->first();
        if ($existing) {
            return redirect()->back()->withErrors([
                'action' => "Permission '{$permName}' is already registered in the system."
            ]);
        }

        \App\Services\PermissionRegistryService::createCustomPermission(
            $module,
            $action,
            $request->description
        );

        return redirect()->back()->with([
            'success' => true, 
            'message' => "Permission '{$permName}' registered successfully"
        ]);
    }

    public function getAccessLevel(){
        $roles = Role::with('permissions')->withCount('users')->get();
        
        $rawGrouped = \App\Services\PermissionRegistryService::getGroupedPermissions();
        $grouped_permissions = [];
        foreach ($rawGrouped as $groupName => $perms) {
            if (!empty($perms)) {
                $grouped_permissions[] = [
                    'groupName' => $groupName,
                    'permissions' => $perms
                ];
            }
        }

        $departments = \App\Models\Department::with('permissions')->withCount('user')->get();

        return inertia('AcessConfig/AccessLevel', [
            'roles' => $roles, 
            'permissions' => $grouped_permissions,
            'departments' => $departments
        ]);
    }

    public function getRoles(){
        $roles = Role::withCount('users')->get();
        return response()->json($roles);
    }

    public function toggleStatus(Request $request){
        $role = Role::find($request->id);
        $role->status = $request->status;
        $role->save();
        return redirect()->back()->with(['success'=>true]);
    }

    public function deleteAccessLevel(Request $request){
        $level = Role::find($request->id);
        $level->delete();
        return redirect()->back()->with(['success'=>true]);
    }

    // public function createPermission(){
    //     $permissions = [
            
    //            [ 'name' => 'create analytics', 'guard_name'=>'web'],
    //             ['name' => 'view analytics'],
    //             ['name' => 'edit analytics'],
    //             ['name' => 'delete analytics']
    //         ,
            
    //             ['name' => 'create chat'],
    //             ['name' => 'view chat'],
    //             ['name' => 'edit chat'],
    //             ['name' => 'delete chat']
    //         ,
            
    //             ['name' => 'create workflow'],
    //             ['name' => 'view workflow'],
    //             ['name' => 'edit workflow'],
    //             ['name' => 'delete workflow']
    //         ,
            
    //            [ 'name' => 'create division task'],
    //             ['name' => 'view division task'],
    //             ['name' => 'edit division task'],
    //             ['name' => 'delete division task']
    //         ,
            
    //             ['name' => 'create mails'],
    //             ['name' => 'view mails'],
    //             ['name' => 'edit mails'],
    //             ['name' => 'delete mails']
    //         ,
            
    //             ['name' => 'create manage users'],
    //             ['name' => 'view manage users'],
    //             ['name' => 'edit manage users'],
    //             ['name' => 'delete manage users']
    //         ,
            
    //             ['name' => 'create branch setup'],
    //             ['name' => 'view branch setup'],
    //             ['name' => 'edit branch setup'],
    //             ['name' => 'delete branch setup']
    //         ,
            
    //             ['name' => 'create access config'],
    //             ['name' => 'view access config'],
    //             ['name' => 'edit access config'],
    //             ['name' => 'delete access config']
    //         ,
            
    //             ['name' => 'create metadata setup'],
    //             ['name' => 'view metadata setup'],
    //             ['name' => 'edit metadata setup'],
    //             ['name' => 'delete metadata setup']
    //         ,
            
    //             ['name' => 'create audit'],
    //             ['name' => 'view audit'],
    //             ['name' => 'edit audit'],
    //             ['name' => 'delete audit']
    //         ,
            
    //             ['name' => 'create recylcle bin'],
    //             ['name' => 'view recylcle bin'],
    //             ['name' => 'edit recylcle bin'],
    //             ['name' => 'delete recylcle bin']
    //         ,
            
    //             ['name' => 'create department'],
    //             ['name' => 'view department'],
    //             ['name' => 'edit department'],
    //             ['name' => 'delete department']
    //         ,
            
    //             ['name' => 'create division'],
    //             ['name' => 'view division'],
    //             ['name' => 'edit division'],
    //             ['name' => 'delete division']
    //         ,
            
    //             ['name' => 'create unit'],
    //             ['name' => 'view unit'],
    //             ['name' => 'edit unit'],
    //             ['name' => 'delete unit']
    //         ,
    //     ];

    //     Permission::insert($permissions);
    //     echo "successfull";
    // }

    public function addPermissionToRole(Request $request)
    {
        $roleId = $request->role_id ?? $request->roleId ?? $request->id;
        
        if (!$roleId) {
            return redirect()->back()->withErrors(['error' => 'No role specified.']);
        }

        $role = is_numeric($roleId) 
            ? Role::find($roleId) 
            : Role::where('name', $roleId)->orWhere('id', $roleId)->first();

        if (!$role) {
            return redirect()->back()->withErrors(['error' => 'Selected role could not be found.']);
        }
        
        $incoming = collect($request->permissions)->flatten()->filter()->unique()->toArray();
        
        if (empty($incoming)) {
            $perms = collect();
        } else {
            $numericIds = array_values(array_filter($incoming, 'is_numeric'));
            $stringNames = array_values(array_filter($incoming, function($v) { return !is_numeric($v); }));

            $perms = Permission::where(function ($query) use ($numericIds, $stringNames) {
                if (!empty($numericIds) && !empty($stringNames)) {
                    $query->whereIn('id', $numericIds)->orWhereIn('name', $stringNames);
                } elseif (!empty($numericIds)) {
                    $query->whereIn('id', $numericIds);
                } elseif (!empty($stringNames)) {
                    $query->whereIn('name', $stringNames);
                }
            })->get();
        }
        
        $role->syncPermissions($perms);
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        \Illuminate\Support\Facades\Cache::flush();

        if ($request->wantsJson() && !$request->header('X-Inertia')) {
            return response()->json([
                'success' => true,
                'message' => 'Role permissions updated successfully.'
            ]);
        }

        return redirect()->back()->with(['success' => true, 'message' => 'Role permissions updated successfully.']);
    }

    public function addPermissionToDepartment(Request $request)
    {
        $deptId = $request->department_id ?? $request->departmentId ?? $request->id;
        
        if (!$deptId) {
            return redirect()->back()->withErrors(['error' => 'No department specified.']);
        }

        $department = is_numeric($deptId)
            ? \App\Models\Department::find($deptId)
            : \App\Models\Department::where('id', $deptId)->orWhere('title', $deptId)->first();

        if (!$department) {
            return redirect()->back()->withErrors(['error' => 'Selected department could not be found.']);
        }
        
        $incoming = collect($request->permissions)->flatten()->filter()->unique()->toArray();
        
        if (empty($incoming)) {
            $perms = collect();
        } else {
            $numericIds = array_values(array_filter($incoming, 'is_numeric'));
            $stringNames = array_values(array_filter($incoming, function($v) { return !is_numeric($v); }));

            $perms = Permission::where(function ($query) use ($numericIds, $stringNames) {
                if (!empty($numericIds) && !empty($stringNames)) {
                    $query->whereIn('id', $numericIds)->orWhereIn('name', $stringNames);
                } elseif (!empty($numericIds)) {
                    $query->whereIn('id', $numericIds);
                } elseif (!empty($stringNames)) {
                    $query->whereIn('name', $stringNames);
                }
            })->get();
        }
        
        $department->syncPermissions($perms);
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        \Illuminate\Support\Facades\Cache::flush();

        if ($request->wantsJson() && !$request->header('X-Inertia')) {
            return response()->json([
                'success' => true,
                'message' => 'Department permissions updated successfully.'
            ]);
        }

        return redirect()->back()->with(['success' => true, 'message' => 'Department permissions updated successfully.']);
    }

    public function addPermissionToUser(Request $request)
    {
        $userId = $request->user_id ?? $request->userId ?? $request->id;
        
        if (!$userId) {
            return redirect()->back()->withErrors(['error' => 'No user specified.']);
        }

        $user = \App\Models\User::find($userId);

        if (!$user) {
            return redirect()->back()->withErrors(['error' => 'Selected user could not be found.']);
        }
        
        $incoming = collect($request->permissions)->flatten()->filter()->unique()->toArray();
        
        // Enforce capability bounding on incoming permissions: only allow permissions whose module is active
        $allowedModules = \App\Services\PermissionRegistryService::getAllowedModulesForUser($user);
        $masterCatalog = \App\Services\PermissionRegistryService::getMasterCatalog();
        
        $validPermNames = [];
        foreach ($incoming as $item) {
            $permName = is_numeric($item) ? Permission::find($item)?->name : $item;
            if (!$permName) continue;
            
            // Check if this permission is within an allowed module for this user
            $moduleFound = null;
            foreach ($masterCatalog as $mod => $pList) {
                if (array_key_exists($permName, $pList)) {
                    $moduleFound = $mod;
                    break;
                }
            }
            
            // Super Admin or allowed module
            if ($user->hasRole('super admin') || !$moduleFound || in_array($moduleFound, $allowedModules)) {
                $validPermNames[] = $permName;
            }
        }
        
        $perms = Permission::whereIn('name', array_unique($validPermNames))->get();
        
        $user->syncPermissions($perms);
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        \Illuminate\Support\Facades\Cache::forget("user_{$user->id}_permissions");
        \Illuminate\Support\Facades\Cache::forget("user_{$user->id}_effective_permissions");
        \Illuminate\Support\Facades\Cache::forget("user_{$user->id}_active_capabilities");
        \Illuminate\Support\Facades\Cache::flush();

        if ($request->wantsJson() && !$request->header('X-Inertia')) {
            return response()->json([
                'success' => true,
                'message' => "User permissions for {$user->name} updated successfully ({$perms->count()} permissions assigned)."
            ]);
        }

        return redirect()->back()->with([
            'success' => true,
            'message' => "User permissions for {$user->name} updated successfully ({$perms->count()} permissions assigned)."
        ]);
    }
}
