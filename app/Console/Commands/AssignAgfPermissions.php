<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AssignAgfPermissions extends Command
{
    protected $signature = 'agf:permissions';
    protected $description = 'Assign all permissions to Accountant-General role';

    public function handle()
    {
        $role = Role::firstOrCreate(['name' => 'Accountant-General']);
        $permissions = Permission::all();
        $role->syncPermissions($permissions);
        $this->info("Assigned " . $permissions->count() . " permissions to Accountant-General.");
    }
}
