<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function createRoles(){
        $roles = [
            ['name' => 'super admin', 'guard_name' => 'web', 'created_at'=>\Carbon\Carbon::now(), 'updated_at'=>\Carbon\Carbon::now()], 
            ['name' => 'admin', 'guard_name' => 'web', 'created_at'=>\Carbon\Carbon::now(), 'updated_at'=>\Carbon\Carbon::now()], 
            ['name' => 'directors', 'guard_name' => 'web', 'created_at'=>\Carbon\Carbon::now(), 'updated_at'=>\Carbon\Carbon::now()], 
            ['name' => 'division heads', 'guard_name' => 'web', 'created_at'=>\Carbon\Carbon::now(), 'updated_at'=>\Carbon\Carbon::now()], 
            ['name' => 'unit heads', 'guard_name' => 'web', 'created_at'=>\Carbon\Carbon::now(), 'updated_at'=>\Carbon\Carbon::now()], 
            ['name' => 'record manager', 'guard_name' => 'web', 'created_at'=>\Carbon\Carbon::now(), 'updated_at'=>\Carbon\Carbon::now()], 
            ['name' => 'super users', 'guard_name' => 'web', 'created_at'=>\Carbon\Carbon::now(), 'updated_at'=>\Carbon\Carbon::now()], 
            ['name' => 'regular users', 'guard_name' => 'web', 'created_at'=>\Carbon\Carbon::now(), 'updated_at'=>\Carbon\Carbon::now()], 
            ['name' => 'data entry', 'guard_name' => 'web', 'created_at'=>\Carbon\Carbon::now(), 'updated_at'=>\Carbon\Carbon::now()],
        ];
        Role::insert($roles);
        echo "successful";
    }
}
