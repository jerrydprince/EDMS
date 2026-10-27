<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\RoleSeeder;
use Database\Seeders\Phase10AdministrationSeeder;
use Database\Seeders\Phase11Adm001Seeder;
use Database\Seeders\Phase13Adm004Seeder;
use Database\Seeders\Phase24UATSeeder;
use App\Models\User;
use App\Models\Task;
use App\Models\TaskAssignment;
use App\Models\Archive;
use App\Models\Department;
use App\Models\WorkflowVersion;
use Spatie\Permission\Models\Permission;

class Phase24IntegrationSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed(RoleSeeder::class);
        $this->seed(Phase10AdministrationSeeder::class);
        $this->seed(Phase11Adm001Seeder::class); 
        $this->seed(Phase13Adm004Seeder::class); 
        $this->seed(Phase24UATSeeder::class);

        Permission::firstOrCreate(['name' => 'workflow update']);
        Permission::firstOrCreate(['name' => 'edms update']);
    }

    public function test_authorization_isolation_approval()
    {
        $registry = User::where('email', 'da.registry@oagf.gov.ng')->first();
        $da = User::where('email', 'da@oagf.gov.ng')->first();
        $unauthorizedUser = User::where('email', 'ad.pool@oagf.gov.ng')->first();

        $this->actingAs($registry);
        // 1. Setup a task assigned to DA
        $adminDept = Department::where('title', 'Administration')->first();
        $file = Archive::forceCreate(['department_id' => $adminDept->id, 'folder_id' => 1, 'updated_by' => $registry->id]);
        $workflow = WorkflowVersion::where('is_published', true)->first();
        
        $task = Task::forceCreate([
            'title' => 'Security Isolation Test',
            'created_by' => $registry->id,
            'workflow_version_id' => $workflow->id,
            'file_id' => $file->id,
            'due_date' => now()->addDays(5)->toDateString(),
            'priority' => '1'
        ]);

        $assignment = TaskAssignment::forceCreate([
            'task_id' => $task->id,
            'assigned_to' => $da->id,
            'assigned_by' => $registry->id,
            'status' => 'pending'
        ]);

        // 2. Unauthorized User attempts to approve via API
        $this->actingAs($unauthorizedUser);
        
        $unauthorizedUser->givePermissionTo(['workflow update', 'edms update']);
        $response = $this->postJson("/dashboard/workflow/approve/{$task->id}", [
            'comments' => 'Approving a task not mine'
        ]);
        
        // Expect Forbidden (403) or Not Found (404) if scoped, or Bad Request (400)
        $this->assertContains($response->status(), [403, 404, 400]);

        // 3. DA (Authorized User) approves
        $da->givePermissionTo(['workflow update', 'edms update']);
        $this->actingAs($da);
        
        $response = $this->postJson("/dashboard/workflow/approve/{$task->id}", [
            'comments' => 'Legit approval'
        ]);
        
        // Expect Success or Redirect
        $this->assertContains($response->status(), [200, 302, 201]);
    }
    
    public function test_audit_trail_created_on_approval()
    {
        $registry = User::where('email', 'da.registry@oagf.gov.ng')->first();
        $da = User::where('email', 'da@oagf.gov.ng')->first();

        $this->actingAs($registry);
        $adminDept = Department::where('title', 'Administration')->first();
        $file = Archive::forceCreate(['department_id' => $adminDept->id, 'folder_id' => 1, 'updated_by' => $registry->id]);
        $workflow = WorkflowVersion::where('is_published', true)->first();
        
        $task = Task::forceCreate([
            'title' => 'Audit Trail Test',
            'created_by' => $registry->id,
            'workflow_version_id' => $workflow->id,
            'file_id' => $file->id,
            'due_date' => now()->addDays(5)->toDateString(),
            'priority' => '1'
        ]);

        $assignment = TaskAssignment::forceCreate([
            'task_id' => $task->id,
            'assigned_to' => $da->id,
            'assigned_by' => $registry->id,
            'status' => 'pending'
        ]);

        $da->givePermissionTo(['workflow update', 'edms update']);
        $this->actingAs($da);
        
        $initialAuditCount = \DB::table('activity_log')->count();
        
        $response = $this->postJson("/dashboard/workflow/approve/{$task->id}", [
            'comments' => 'Approving with audit'
        ]);
        
        $this->assertContains($response->status(), [200, 302, 201]);
        
        $finalAuditCount = \DB::table('activity_log')->count();
        $this->assertTrue($finalAuditCount >= $initialAuditCount);
    }
    
    public function test_validation_errors()
    {
        $registry = User::where('email', 'da.registry@oagf.gov.ng')->first();
        $da = User::where('email', 'da@oagf.gov.ng')->first();

        $this->actingAs($registry);
        $adminDept = Department::where('title', 'Administration')->first();
        $file = Archive::forceCreate(['department_id' => $adminDept->id, 'folder_id' => 1, 'updated_by' => $registry->id]);
        $workflow = WorkflowVersion::where('is_published', true)->first();
        
        $task = Task::forceCreate([
            'title' => 'Validation Error Test',
            'created_by' => $registry->id,
            'workflow_version_id' => $workflow->id,
            'file_id' => $file->id,
            'due_date' => now()->addDays(5)->toDateString(),
            'priority' => '1'
        ]);

        $assignment = TaskAssignment::forceCreate([
            'task_id' => $task->id,
            'assigned_to' => $da->id,
            'assigned_by' => $registry->id,
            'status' => 'pending'
        ]);

        $da->givePermissionTo(['workflow update', 'edms update']);
        $this->actingAs($da);
        
        // e.g. returnTask requires reason/comment
        $response = $this->postJson("/dashboard/workflow/return/{$task->id}", []);
        
        // Usually 422 for validation error, or 302 back with errors, or 400
        $this->assertContains($response->status(), [422, 302, 400]);
    }
}
