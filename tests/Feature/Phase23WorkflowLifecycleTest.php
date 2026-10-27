<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Department;
use App\Models\Workflow;
use App\Models\WorkflowVersion;
use App\Models\WorkflowStep;
use App\Models\WorkflowTransition;
use Spatie\Permission\Models\Permission;

class Phase23WorkflowLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_workflow_lifecycle()
    {
        \Illuminate\Database\Eloquent\Model::unguard();
        $dept = Department::create(['title' => 'IT']);
        \Illuminate\Database\Eloquent\Model::reguard();
        
        $user = User::factory()->create(['department_id' => $dept->id]);
        
        $permissionView = Permission::firstOrCreate(['name' => 'workflow view']);
        $permissionCreate = Permission::firstOrCreate(['name' => 'workflow create']);
        $permissionEdmsView = Permission::firstOrCreate(['name' => 'edms view']);
        $permissionEdmsUpdate = Permission::firstOrCreate(['name' => 'edms update']);
        
        $user->givePermissionTo([$permissionView, $permissionCreate, $permissionEdmsView, $permissionEdmsUpdate]);

        // 1. Create Workflow
        $workflow = Workflow::create(['title' => 'Lifecycle Test', 'created_by' => $user->id]);
        $version = $workflow->versions()->create(['version_number' => 1, 'is_published' => true]);
        
        $step1 = $version->steps()->create(['name' => 'Start', 'is_start_step' => true]);
        $step2 = $version->steps()->create(['name' => 'End', 'is_end_step' => true]);
        
        $transition = WorkflowTransition::create([
            'workflow_version_id' => $version->id,
            'from_step_id' => $step1->id,
            'to_step_id' => $step2->id,
            'action_name' => 'Approve'
        ]);

        // 2. Instantiate Task via Eloquent for test predictability
        $this->actingAs($user); // Use default web guard for dashboard routes
        $task = \App\Models\Task::create([
            'workflow_version_id' => $version->id,
            'title' => 'My Lifecycle Task',
            'due_date' => now()->addDays(5)->format('Y-m-d'),
            'status' => 'in progress',
            'created_by' => $user->id,
            'current_step_id' => $step1->id
        ]);
        
        $taskId = $task->id;
        
        \App\Models\TaskAssignment::create([
            'task_id' => $taskId,
            'assigned_to' => $user->id,
            'assigned_by' => $user->id,
            'status' => 'pending'
        ]);

        \Illuminate\Support\Facades\DB::table('task_active_steps')->insert([
            'task_id' => $taskId,
            'step_id' => $step1->id,
            'status' => 'in_progress',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 3. Execute Transition
        $response = $this->postJson("/dashboard/generic-workflow/instances/{$taskId}/execute", [
            'transition_ids' => [$transition->id]
        ]);
        
        $response->assertStatus(200);

        // Verify task is completed
        $this->assertDatabaseHas('task', [
            'id' => $taskId,
            'status' => 'completed',
            'current_step_id' => $step2->id
        ]);
    }
}
