<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function createPermission(){
        $permissions_data = [
            'branch',
            'metadata',
            'document type',
            'archive',
        ];
        
        $permissions = [];
        foreach ($permissions_data as $perm) {
           $arr = ['create', 'read', 'update', 'delete'];
           foreach($arr as $val){
            $new_arr = [
                'name' => $val." ".$perm,
                'guard_name' => 'web',
                'created_at' =>\Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ];
            array_push($permissions, $new_arr);
           }
        }

        $permission = Permission::insert($permissions);
        echo "success";
    }
}
