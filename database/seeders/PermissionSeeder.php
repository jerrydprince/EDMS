<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions_data = [
            'department',
            'division',
            'unit',
            'metadata setup',
            'document type',
            'archiving',
            'analytics',
            'chat',
            'workflow',
            'mails',
            'manage users',
            'branch setup',
            'access config',
            'audit',
            'recycle bin',
            'registry',
            'agf registry',
            'edms',
            'pms'
        ];
        
        $permissions = [];
        foreach ($permissions_data as $perm) {
           $arr = ['create', 'view', 'update', 'delete'];
           foreach($arr as $val){
            $new_arr = [
                'name' => $perm." ".$val,
                'guard_name' => 'web',
                'created_at' =>\Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ];
            array_push($permissions, $new_arr);
           }
        }

        $permission = Permission::insert($permissions);
    }
}
