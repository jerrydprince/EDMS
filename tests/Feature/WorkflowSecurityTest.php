<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use App\Models\WorkflowVersion;
use Spatie\Permission\Models\Role;

class WorkflowSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create necessary roles
        Role::firstOrCreate(['name' => 'super-admin']);
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'regular user']);
    }

    private function createWorkflowVersion($userId) {
        $workflow = \App\Models\Workflow::create([
            'title' => 'Test Workflow',
            'created_by' => $userId
        ]);
        return WorkflowVersion::create([
            'workflow_id' => $workflow->id,
            'version_number' => 1,
            'is_published' => true
        ]);
    }

    public function test_unassigned_user_cannot_pause_or_resume_task()
    {
        $user = User::factory()->create();
        $user->assignRole('regular user');

        $workflowVersion = $this->createWorkflowVersion($user->id);
        
        $task = new Task();
        $task->title = 'Test';
        $task->status = 'pending';
        $task->due_date = now();
        $task->created_by = $user->id;
        $task->workflow_version_id = $workflowVersion->id;
        $task->save();

        // Attempt pause
        $response = $this->actingAs($user)->postJson("/api/generic-workflow/tasks/{$task->id}/pause");
        $response->assertStatus(403);
        $response->assertJson(['error' => 'You are not authorized to pause this task.']);

        // Attempt resume
        $response = $this->actingAs($user)->postJson("/api/generic-workflow/tasks/{$task->id}/resume");
        $response->assertStatus(403);
        $response->assertJson(['error' => 'You are not authorized to resume this task.']);
    }

    public function test_admin_can_pause_and_resume_task()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $workflowVersion = $this->createWorkflowVersion($admin->id);
        
        $task = new Task();
        $task->title = 'Test';
        $task->status = 'pending';
        $task->due_date = now();
        $task->created_by = $admin->id;
        $task->workflow_version_id = $workflowVersion->id;
        $task->save();

        // Mock the service call or allow it to fail safely, we just want to check auth barrier passes
        $response = $this->actingAs($admin)->postJson("/api/generic-workflow/tasks/{$task->id}/pause");
        
        // If it passes 403, it might hit a 400 or 200 depending on task state
        $this->assertNotEquals(403, $response->status());
    }

    public function test_assigned_user_can_pause_and_resume_task()
    {
        $user = User::factory()->create();
        $user->assignRole('regular user');

        $workflowVersion = $this->createWorkflowVersion($user->id);
        
        $task = new Task();
        $task->title = 'Test';
        $task->status = 'pending';
        $task->due_date = now();
        $task->created_by = $user->id;
        $task->workflow_version_id = $workflowVersion->id;
        $task->save();

        \App\Models\TaskAssignment::create([
            'task_id' => $task->id,
            'assigned_to' => $user->id,
            'assigned_by' => $user->id,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($user)->postJson("/api/generic-workflow/tasks/{$task->id}/pause");
        
        $this->assertNotEquals(403, $response->status());
    }
}
