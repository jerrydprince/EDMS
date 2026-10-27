<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use App\Models\WorkflowTransition;
use App\Models\WorkflowVersion;
use App\Models\Task;
use App\Models\TaskAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GenericWorkflowInstanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup Roles and Permissions
        $permissionCreate = Permission::create(['name' => 'workflow create']);
        $permissionView = Permission::create(['name' => 'workflow view']);
        $permissionEdmsView = Permission::firstOrCreate(['name' => 'edms view']);
        $permissionEdmsUpdate = Permission::firstOrCreate(['name' => 'edms update']);
        
        $role = Role::create(['name' => 'admin']);
        $role->givePermissionTo([$permissionCreate, $permissionView, $permissionEdmsView, $permissionEdmsUpdate]);
    }

    public function test_get_available_actions_returns_valid_transitions()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $this->actingAs($user, 'sanctum');

        // Setup generic workflow blueprint
        $workflow = \App\Models\Workflow::create(['title' => 'Invoice Processing', 'created_by' => $user->id]);
        $version = $workflow->versions()->create(['version_number' => 1, 'is_published' => true]);
        
        $step1 = $version->steps()->create([
            'name' => 'Draft',
            'is_start_step' => true
        ]);
        
        $step2 = $version->steps()->create([
            'name' => 'Manager Approval',
        ]);
        
        $transition = WorkflowTransition::create([
            'workflow_version_id' => $version->id,
            'from_step_id' => $step1->id,
            'to_step_id' => $step2->id,
            'action_name' => 'Submit for Approval'
        ]);

        // Create Task (Instance)
        $task = new Task();
        $task->title = 'Test Invoice';
        $task->workflow_version_id = $version->id;
        $task->current_step_id = $step1->id;
        $task->due_date = now();
        $task->status = 'in progress';
        $task->created_by = $user->id;
        $task->save();
        
        // Ensure user is assigned
        \App\Models\TaskAssignment::create([
            'task_id' => $task->id,
            'assigned_to' => $user->id,
            'assigned_by' => $user->id,
            'status' => 'pending'
        ]);

        \Illuminate\Support\Facades\DB::table('task_active_steps')->insert([
            'task_id' => $task->id,
            'step_id' => $step1->id,
            'status' => 'in_progress',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->get("/api/generic-workflow/tasks/{$task->id}/actions");
        if ($response->status() !== 200) {
            dd($response->exception ?? $response->content());
        }
        
        $response->assertStatus(200);
        $response->assertJsonPath('actions.0.action_name', 'Submit for Approval');
        $response->assertJsonPath('actions.0.to_step.name', 'Manager Approval');
    }
    
    public function test_cannot_get_actions_if_not_assigned_and_not_admin()
    {
        $user = User::factory()->create(); // no role
        $this->actingAs($user, 'sanctum');

        $workflow = Workflow::create(['title' => 'Invoice Processing', 'created_by' => 999]);
        $version = $workflow->versions()->create(['version_number' => 1, 'is_published' => true]);
        $step1 = $version->steps()->create([
            'name' => 'Draft',
            'is_start_step' => true
        ]);
        
        $creator = \App\Models\User::factory()->create();

        $task = new \App\Models\Task();
        $task->title = 'Test Invoice';
        $task->workflow_version_id = $version->id;
        $task->current_step_id = $step1->id;
        $task->due_date = now();
        $task->status = 'in progress';
        $task->created_by = $creator->id;
        $task->save();

        $response = $this->get("/dashboard/generic-workflow/instances/{$task->id}/actions");
        
        // Either 403 due to middleware or empty actions due to service logic
        if ($response->status() === 403) {
            $response->assertStatus(403);
        } else {
            $response->assertStatus(200);
            $this->assertEmpty($response->json('actions'));
        }
    }

    public function test_execute_action_progresses_workflow()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $this->actingAs($user, 'sanctum');

        $this->withoutExceptionHandling();
        $workflow = \App\Models\Workflow::create(['title' => 'Invoice Processing', 'created_by' => $user->id]);
        $version = $workflow->versions()->create(['version_number' => 1, 'is_published' => true]);
        
        $step1 = $version->steps()->create(['name' => 'Draft', 'is_start_step' => true]);
        $step2 = $version->steps()->create(['name' => 'Manager Approval']);
        
        $transition = WorkflowTransition::create([
            'workflow_version_id' => $version->id,
            'from_step_id' => $step1->id,
            'to_step_id' => $step2->id,
            'action_name' => 'Submit for Approval'
        ]);

        $task = new Task();
        $task->title = 'Test Invoice 2';
        $task->workflow_version_id = $version->id;
        $task->current_step_id = $step1->id;
        $task->due_date = now();
        $task->status = 'in progress';
        $task->created_by = $user->id;
        $task->save();

        \App\Models\TaskAssignment::create([
            'task_id' => $task->id,
            'assigned_to' => $user->id,
            'assigned_by' => $user->id,
            'status' => 'pending'
        ]);

        \Illuminate\Support\Facades\DB::table('task_active_steps')->insert([
            'task_id' => $task->id,
            'step_id' => $step1->id,
            'status' => 'in_progress',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->post("/api/generic-workflow/tasks/{$task->id}/execute", [
            'transition_ids' => [$transition->id],
            'comments' => 'Looks good to me'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Action executed successfully.']);

        $task->refresh();
        
        $this->assertEquals($step2->id, $task->current_step_id);
        
        $this->assertDatabaseHas('workflow_logs', [
            'task_id' => $task->id,
            'action_type' => 'Submit for Approval',
            'comments' => 'Looks good to me'
        ]);
    }
}
