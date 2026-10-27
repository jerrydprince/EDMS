<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Department;
use App\Models\Archive;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Phase23AuthorizationPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_archive_policy_prevents_cross_department_access()
    {
        \Illuminate\Database\Eloquent\Model::unguard();
        $dept1 = Department::create(['title' => 'Finance']);
        $dept2 = Department::create(['title' => 'HR']);
        \Illuminate\Database\Eloquent\Model::reguard();

        $user1 = User::factory()->create(['department_id' => $dept1->id]);
        $user2 = User::factory()->create(['department_id' => $dept2->id]);

        $permission = Permission::firstOrCreate(['name' => 'edms view']);
        $user1->givePermissionTo($permission);
        $user2->givePermissionTo($permission);

        \Illuminate\Database\Eloquent\Model::unguard();
        $this->actingAs($user1);
        $folder = \App\Models\Folder::create(['department_id' => $dept1->id, 'name' => 'Test Folder', 'number' => '123', 'path' => '/dummy/path']);
        $archive = Archive::create(['department_id' => $dept1->id, 'folder_id' => $folder->id, 'updated_by' => $user1->id]);
        \Illuminate\Database\Eloquent\Model::reguard();

        // User 1 is in same department, should have access
        $this->assertTrue($user1->can('view', $archive));

        // User 2 is in different department, should NOT have access
        $this->assertFalse($user2->can('view', $archive));
    }
}
