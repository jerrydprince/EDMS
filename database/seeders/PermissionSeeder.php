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
        // Delete all old permissions first to avoid orphans or duplicates?
        // Let's just insert new ones, and if it fails, maybe clear first.
        // Actually, just clear all existing permissions to avoid clutter:
        \Illuminate\Support\Facades\DB::table('permissions')->delete();

        $permissions_data = [
            'department' => ['view departments', 'create departments', 'edit departments', 'archive departments'],
            'division' => ['view divisions', 'create divisions', 'edit divisions', 'assign division heads'],
            'unit' => ['view units', 'create units', 'edit units', 'assign unit heads'],
            'metadata setup' => ['view metadata config', 'create metadata fields', 'edit metadata fields', 'delete metadata fields'],
            'document type' => ['view doc types', 'create doc types', 'edit doc types', 'delete doc types'],
            'archiving' => ['view archives', 'archive files', 'restore archives'],
            'analytics' => ['view analytics', 'export analytics reports', 'generate custom reports'],
            'chat' => ['view chats', 'create group chat', 'send message', 'manage groups'],
            'workflow' => ['view workflow', 'initiate workflow', 'approve workflow', 'reject workflow', 'delegate task', 'forward task', 'terminate workflow'],
            'mails' => ['view mail', 'compose mail', 'reply mail', 'forward mail', 'archive mail', 'delete mail', 'star mail'],
            'manage users' => ['view users', 'create users', 'edit users', 'delete users', 'block users', 'reset passwords'],
            'branch setup' => ['view branches', 'create branches', 'edit branches', 'delete branches'],
            'access config' => ['view access config', 'manage roles', 'assign permissions'],
            'audit' => ['view audit logs', 'export audit logs', 'filter audit logs'],
            'recycle bin' => ['view recycle bin', 'restore items', 'permanently delete items'],
            'registry' => ['view registry', 'add files to registry', 'dispatch files', 'receive files'],
            'agf registry' => ['view agf registry', 'add files to agf registry', 'dispatch agf files', 'receive agf files'],
            'edms' => ['view documents', 'upload documents', 'download documents', 'share documents', 'version documents', 'delete documents', 'manage folder structures'],
            'pms' => ['view performance', 'manage performance', 'appraise performance']
        ];
        
        $permissions = [];
        foreach ($permissions_data as $module => $actions) {
           foreach($actions as $action){
            $permissions[] = [
                'name' => $action,
                'guard_name' => 'web',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ];
           }
        }

        Permission::insert($permissions);
    }
}
