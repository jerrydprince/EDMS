<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use App\Models\WorkflowTransition;
use App\Models\WorkflowVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WorkflowBuilderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup Roles and Permissions
        $permissionCreate = Permission::create(['name' => 'branch setup create']);
        $permissionView = Permission::create(['name' => 'branch setup view']);
        
        $role = Role::create(['name' => 'admin']);
        $role->givePermissionTo([$permissionCreate, $permissionView]);
    }

    public function test_unauthenticated_user_cannot_access_builder()
    {
        $response = $this->get('/dashboard/workflow-builder');
        $response->assertRedirect('/login');
    }

    public function test_unauthorized_user_cannot_access_builder()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/dashboard/workflow-builder');
        $response->assertStatus(403);
    }

    public function test_authorized_user_can_access_builder()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        
        $this->actingAs($user);

        $response = $this->get('/dashboard/workflow-builder');
        $response->assertStatus(200);
    }

    public function test_can_store_workflow_and_initial_version()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $this->actingAs($user);

        $response = $this->post('/dashboard/workflow-builder', [
            'title' => 'Leave Approval Workflow',
            'emoji' => '🏝️'
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('workflows', [
            'title' => 'Leave Approval Workflow',
            'emoji' => '🏝️'
        ]);

        $workflow = Workflow::where('title', 'Leave Approval Workflow')->first();

        $this->assertDatabaseHas('workflow_versions', [
            'workflow_id' => $workflow->id,
            'version_number' => 1,
            'is_published' => false
        ]);
    }

    public function test_department_isolation_on_step_creation()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $user->department_id = 999;
        $user->save();
        
        $this->actingAs($user);

        $workflow = Workflow::create(['title' => 'Test', 'created_by' => $user->id]);
        $version = $workflow->versions()->create(['version_number' => 1]);

        \App\Models\Department::unguard();
        $dept = \App\Models\Department::create(['id' => 888, 'title' => 'Test Dept']);
        \App\Models\Department::reguard();
        
        // Attempt to create a step in a different department
        $response = $this->post("/dashboard/workflow-builder/version/{$version->id}/steps", [
            'name' => 'HOD Review',
            'step_type' => 'approval',
            'department_id' => $dept->id // Different department
        ]);

        $response->assertStatus(403);
    }

    public function test_can_store_step_in_own_department_or_global()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $user->department_id = 999;
        $user->save();
        
        $this->actingAs($user);

        $workflow = Workflow::create(['title' => 'Test', 'created_by' => $user->id]);
        $version = $workflow->versions()->create(['version_number' => 1]);

        // Global Step
        $response = $this->post("/dashboard/workflow-builder/version/{$version->id}/steps", [
            'name' => 'Global Review',
            'step_type' => 'approval',
            'department_id' => null
        ]);
        $response->assertRedirect();
        
        $this->assertDatabaseHas('workflow_steps', [
            'name' => 'Global Review',
            'workflow_version_id' => $version->id,
            'department_id' => null
        ]);

        // Department Specific Step
        \App\Models\Department::unguard();
        $dept = Department::create(['title' => 'HR', 'id' => 999]);
        \App\Models\Department::reguard();
        
        $response2 = $this->post("/dashboard/workflow-builder/version/{$version->id}/steps", [
            'name' => 'HR Review',
            'step_type' => 'approval',
            'department_id' => 999
        ]);
        $response2->assertRedirect();
        
        $this->assertDatabaseHas('workflow_steps', [
            'name' => 'HR Review',
            'department_id' => 999
        ]);
    }

    public function test_can_approve_workflow_version()
    {
        $user = User::factory()->create(['approval_pin' => '1234']);
        $user->assignRole('admin');
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'workflow approve']);
        $user->givePermissionTo('workflow approve');
        $this->actingAs($user);

        $workflow = Workflow::create(['title' => 'Test', 'created_by' => $user->id]);
        $version = $workflow->versions()->create(['version_number' => 1, 'is_published' => false]);

        $response = $this->post("/dashboard/workflow-builder/version/{$version->id}/approve", [
            'approval_pin' => '1234'
        ]);
        $response->assertRedirect();

        $this->assertDatabaseHas('workflow_versions', [
            'id' => $version->id,
            'is_approved' => true,
            'approved_by_id' => $user->id
        ]);
    }

    public function test_can_publish_workflow_version()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $this->actingAs($user);

        $workflow = Workflow::create(['title' => 'Test', 'created_by' => $user->id]);
        $version1 = $workflow->versions()->create(['version_number' => 1, 'is_published' => true]);
        $version2 = $workflow->versions()->create(['version_number' => 2, 'is_published' => false, 'is_approved' => true]);

        $response = $this->post("/dashboard/workflow-builder/version/{$version2->id}/publish");
        $response->assertRedirect();

        // Check if v2 is published and v1 is unpublished
        $this->assertDatabaseHas('workflow_versions', [
            'id' => $version2->id,
            'is_published' => true
        ]);
        
        $this->assertDatabaseHas('workflow_versions', [
            'id' => $version1->id,
            'is_published' => false
        ]);
    }

    public function test_can_rollback_workflow_version()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'workflow approve']);
        $user->givePermissionTo('workflow approve');
        $this->actingAs($user);

        $workflow = Workflow::create(['title' => 'Test', 'created_by' => $user->id]);
        $version1 = $workflow->versions()->create(['version_number' => 1, 'is_published' => false, 'is_approved' => true]);
        $version2 = $workflow->versions()->create(['version_number' => 2, 'is_published' => true, 'is_approved' => true]);

        $response = $this->post("/dashboard/workflow-builder/version/{$version2->id}/rollback", [
            'rollback_to_version_id' => $version1->id
        ]);
        $response->assertRedirect();

        $this->assertDatabaseHas('workflow_versions', [
            'id' => $version2->id,
            'is_published' => false
        ]);
        
        $this->assertDatabaseHas('workflow_versions', [
            'id' => $version1->id,
            'is_published' => true
        ]);
    }

    public function test_cannot_approve_workflow_with_invalid_pin()
    {
        $user = User::factory()->create(['approval_pin' => '1234']);
        $user->assignRole('admin');
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'workflow approve']);
        $user->givePermissionTo('workflow approve');
        $this->actingAs($user);

        $workflow = Workflow::create(['title' => 'Test', 'created_by' => $user->id]);
        $version = $workflow->versions()->create(['version_number' => 1, 'is_published' => false]);

        $response = $this->post("/dashboard/workflow-builder/version/{$version->id}/approve", [
            'approval_pin' => '9999' // Invalid PIN
        ]);
        
        $response->assertStatus(403);
        $this->assertDatabaseHas('workflow_versions', [
            'id' => $version->id,
            'is_approved' => false
        ]);
    }

    public function test_cannot_approve_workflow_without_permission()
    {
        $user = User::factory()->create(['approval_pin' => '1234']);
        $user->assignRole('admin');
        // Do NOT give 'workflow approve' permission
        $this->actingAs($user);

        $workflow = Workflow::create(['title' => 'Test', 'created_by' => $user->id]);
        $version = $workflow->versions()->create(['version_number' => 1, 'is_published' => false]);

        $response = $this->post("/dashboard/workflow-builder/version/{$version->id}/approve", [
            'approval_pin' => '1234'
        ]);
        
        $response->assertStatus(403);
    }

    public function test_cannot_approve_workflow_from_another_department()
    {
        $user = User::factory()->create(['approval_pin' => '1234']);
        $user->assignRole('admin');
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'workflow approve']);
        $user->givePermissionTo('workflow approve');
        $user->department_id = 999;
        $user->save();
        
        $this->actingAs($user);

        // Create department 888
        \App\Models\Department::unguard();
        \App\Models\Department::create(['id' => 888, 'title' => 'Another Dept']);
        \App\Models\Department::reguard();

        // Workflow belongs to department 888
        $workflow = Workflow::create(['title' => 'Test', 'created_by' => $user->id, 'department_id' => 888]);
        $version = $workflow->versions()->create(['version_number' => 1, 'is_published' => false]);

        $response = $this->post("/dashboard/workflow-builder/version/{$version->id}/approve", [
            'approval_pin' => '1234'
        ]);
        
        $response->assertStatus(403);
    }

    public function test_cannot_rollback_to_unapproved_version()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'workflow approve']);
        $user->givePermissionTo('workflow approve');
        $this->actingAs($user);

        $workflow = Workflow::create(['title' => 'Test', 'created_by' => $user->id]);
        // Version 1 is NOT approved
        $version1 = $workflow->versions()->create(['version_number' => 1, 'is_published' => false, 'is_approved' => false]);
        $version2 = $workflow->versions()->create(['version_number' => 2, 'is_published' => true, 'is_approved' => true]);

        $response = $this->post("/dashboard/workflow-builder/version/{$version2->id}/rollback", [
            'rollback_to_version_id' => $version1->id
        ]);
        
        $response->assertStatus(403);
    }
}
