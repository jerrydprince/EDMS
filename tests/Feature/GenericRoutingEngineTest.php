<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use App\Models\WorkflowTransition;
use App\Models\WorkflowCondition;
use App\Models\WorkflowVersion;
use App\Models\Task;
use App\Models\TaskAssignment;
use App\Services\GenericWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class GenericRoutingEngineTest extends TestCase
{
    use RefreshDatabase;

    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        Model::unguard();
        
        $permissionCreate = Permission::create(['name' => 'workflow create']);
        $permissionView = Permission::create(['name' => 'workflow view']);
        
        $role = Role::create(['name' => 'admin']);
        $role->givePermissionTo([$permissionCreate, $permissionView]);
        
        $this->service = app(GenericWorkflowService::class);
    }

    public function test_sequential_routing_with_task_active_steps()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        
        $workflow = Workflow::create(['title' => 'Routing Test', 'created_by' => $user->id]);
        $version = $workflow->versions()->create(['version_number' => 1, 'is_published' => true]);
        
        $step1 = $version->steps()->create(['name' => 'Draft', 'is_start_step' => true]);
        $step2 = $version->steps()->create(['name' => 'Manager Approval']);
        
        $transition = WorkflowTransition::create([
            'workflow_version_id' => $version->id,
            'from_step_id' => $step1->id,
            'to_step_id' => $step2->id,
            'action_name' => 'Submit'
        ]);

        $task = Task::create([
            'title' => 'Test Task',
            'workflow_version_id' => $version->id,
            'due_date' => now()->addDays(5)->format('Y-m-d')
        ]);
        
        // Start workflow natively through service
        $this->service->start($task, $user, [$user->id]);
        
        $this->assertDatabaseHas('task_active_steps', [
            'task_id' => $task->id,
            'step_id' => $step1->id,
            'status' => 'in_progress'
        ]);
        
        // Execute Transition
        $this->service->executeAction($task, $user, $transition, [$user->id], 'Moving to manager');
        
        $this->assertDatabaseHas('task_active_steps', [
            'task_id' => $task->id,
            'step_id' => $step1->id,
            'status' => 'completed'
        ]);
        
        $this->assertDatabaseHas('task_active_steps', [
            'task_id' => $task->id,
            'step_id' => $step2->id,
            'status' => 'in_progress'
        ]);
    }

    public function test_conditional_routing_blocks_bypasses()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        
        $workflow = Workflow::create(['title' => 'Conditional Test', 'created_by' => $user->id]);
        $version = $workflow->versions()->create(['version_number' => 1, 'is_published' => true]);
        
        $step1 = $version->steps()->create(['name' => 'Draft', 'is_start_step' => true]);
        $step2 = $version->steps()->create(['name' => 'Big Approval']);
        
        $transition = WorkflowTransition::create([
            'workflow_version_id' => $version->id,
            'from_step_id' => $step1->id,
            'to_step_id' => $step2->id,
            'action_name' => 'Submit Big Amount'
        ]);
        
        // Add Condition: priority = 'high'
        WorkflowCondition::create([
            'workflow_transition_id' => $transition->id,
            'field' => 'priority',
            'operator' => '=',
            'value' => 'high'
        ]);

        // Create Task with priority = 'low' (Fails Condition)
        $task = Task::create([
            'title' => 'Low Priority Task',
            'workflow_version_id' => $version->id,
            'priority' => 'low',
            'due_date' => now()->addDays(5)->format('Y-m-d')
        ]);
        
        $this->service->start($task, $user, [$user->id]);
        
        // Ensure getAvailableActions filters it out
        $actions = $this->service->getAvailableActions($task, $user);
        $this->assertCount(0, $actions);
        
        // Ensure executeAction blocks illegal bypass
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Transition conditions not met. Illegal Bypass Blocked.');
        $this->service->executeAction($task, $user, $transition, [$user->id], 'Hack attempt');
    }

    public function test_organisational_upward_routing_auto_assigns_hod()
    {
        Role::create(['name' => 'HOD']);
        
        $dept = Department::create(['title' => 'IT Department']);
        
        $initiator = User::factory()->create(['department_id' => $dept->id]);
        $hod = User::factory()->create(['department_id' => $dept->id, 'is_department_head' => 1]);
        $hod->assignRole('HOD');
        
        $workflow = Workflow::create(['title' => 'HOD Approval', 'created_by' => $initiator->id]);
        $version = $workflow->versions()->create(['version_number' => 1, 'is_published' => true]);
        
        $step1 = $version->steps()->create(['name' => 'Draft', 'is_start_step' => true]);
        // HOD Role Required
        $step2 = $version->steps()->create(['name' => 'HOD Approval', 'role_required' => 'HOD']);
        
        $transition = WorkflowTransition::create([
            'workflow_version_id' => $version->id,
            'from_step_id' => $step1->id,
            'to_step_id' => $step2->id,
            'action_name' => 'Send to HOD'
        ]);

        $task = Task::create([
            'title' => 'My IT Request',
            'workflow_version_id' => $version->id,
            'created_by' => $initiator->id,
            'due_date' => now()->addDays(5)->format('Y-m-d')
        ]);
        
        $this->service->start($task, $initiator, [$initiator->id]);
        
        // Execute Action with EMPTY assignments array
        $this->service->executeAction($task, $initiator, $transition, [], 'Auto route please');
        
        // Verify HOD received the task assignment
        $this->assertDatabaseHas('task_assignments', [
            'task_id' => $task->id,
            'assigned_to' => $hod->id,
            'status' => 'pending'
        ]);
    }
}
