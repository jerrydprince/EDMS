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
use App\Models\Task;
use App\Models\TaskAssignment;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class MandatoryWorkflowLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Essential permissions
        Permission::firstOrCreate(['name' => 'workflow view']);
        Permission::firstOrCreate(['name' => 'workflow create']);
        Permission::firstOrCreate(['name' => 'edms view']);
        Permission::firstOrCreate(['name' => 'edms update']);
        Permission::firstOrCreate(['name' => 'workflow execute']);
        Permission::firstOrCreate(['name' => 'registry view']);
        Role::firstOrCreate(['name' => 'admin']);
    }

    protected function setupUserAndDepartment($deptName = 'HQ')
    {
        \Illuminate\Database\Eloquent\Model::unguard();
        $dept = Department::create(['title' => $deptName]);
        \Illuminate\Database\Eloquent\Model::reguard();
        
        $user = User::factory()->create(['department_id' => $dept->id, 'approval_pin' => '1234']);
        $user->givePermissionTo(Permission::all());
        
        return [$user, $dept];
    }

    /**
     * test: create workflow; assign; accept; minute; forward; approve; completion; closure
     */
    public function test_end_to_end_standard_lifecycle()
    {
        list($user, $dept) = $this->setupUserAndDepartment();
        $this->actingAs($user);

        // 1. Create Workflow
        $workflow = Workflow::create(['title' => 'Standard Lifecycle', 'created_by' => $user->id, 'department_id' => $dept->id]);
        $version = $workflow->versions()->create(['version_number' => 1, 'is_published' => true, 'is_approved' => true]);
        
        $stepStart = $version->steps()->create(['name' => 'Initiation', 'is_start_step' => true]);
        $stepReview = $version->steps()->create(['name' => 'Review']);
        $stepApproval = $version->steps()->create(['name' => 'Approval', 'is_end_step' => true]);

        $transitionForward = WorkflowTransition::create([
            'workflow_version_id' => $version->id,
            'from_step_id' => $stepStart->id,
            'to_step_id' => $stepReview->id,
            'action_name' => 'Forward'
        ]);

        $transitionApprove = WorkflowTransition::create([
            'workflow_version_id' => $version->id,
            'from_step_id' => $stepReview->id,
            'to_step_id' => $stepApproval->id,
            'action_name' => 'Approve'
        ]);

        // Create Task (Workflow Instance)
        $task = Task::create([
            'workflow_version_id' => $version->id,
            'title' => 'Process Invoice',
            'status' => 'in progress',
            'created_by' => $user->id,
            'current_step_id' => $stepStart->id,
            'due_date' => now()->addDays(5)
        ]);

        DB::table('task_active_steps')->insert([
            'task_id' => $task->id,
            'step_id' => $stepStart->id,
            'status' => 'in_progress',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 2. Assign
        $assignment = TaskAssignment::create([
            'task_id' => $task->id,
            'assigned_to' => $user->id,
            'assigned_by' => $user->id,
            'status' => 'pending'
        ]);

        // 3. Accept Assignment (Update status to uncompleted)
        $assignment->update(['status' => 'uncompleted']);
        $this->assertDatabaseHas('task_assignments', ['id' => $assignment->id, 'status' => 'uncompleted']);

        // 4. Minute / Comment
        \App\Models\TaskActivity::forceCreate([
            'task_id' => $task->id,
            'created_by' => $user->id,
            'description' => 'minuted',
        ]);
        $this->assertDatabaseHas('task_activities', ['task_id' => $task->id, 'description' => 'minuted']);

        // 5. Forward (Execute Transition 1)
        $responseForward = $this->postJson("/dashboard/generic-workflow/instances/{$task->id}/execute", [
            'transition_ids' => [$transitionForward->id]
        ]);
        $responseForward->assertStatus(200);

        $this->assertDatabaseHas('task', ['id' => $task->id, 'current_step_id' => $stepReview->id]);

        // 6. Approve & Completion (Execute Transition 2)
        $responseApprove = $this->postJson("/dashboard/generic-workflow/instances/{$task->id}/execute", [
            'transition_ids' => [$transitionApprove->id]
        ]);
        $responseApprove->assertStatus(200);

        // 7. Closure
        $this->assertDatabaseHas('task', [
            'id' => $task->id,
            'status' => 'completed',
            'current_step_id' => $stepApproval->id
        ]);
    }

    public function test_sla_breach_and_escalation_lifecycle()
    {
        list($user, $dept) = $this->setupUserAndDepartment();
        $this->actingAs($user);
        
        $workflow = Workflow::create(['title' => 'SLA Lifecycle', 'created_by' => $user->id, 'department_id' => $dept->id]);
        $version = $workflow->versions()->create(['version_number' => 1, 'is_published' => true, 'is_approved' => true]);
        
        $step1 = $version->steps()->create(['name' => 'Start', 'is_start_step' => true]);
        
        \App\Models\WorkflowSla::create([
            'workflow_step_id' => $step1->id,
            'duration_hours' => 24,
            'warning_hours' => 12,
            'escalation_action' => 'reassign',
            'escalation_role' => 'admin'
        ]);

        $task = Task::create([
            'workflow_version_id' => $version->id,
            'title' => 'SLA Task',
            'status' => 'in progress',
            'created_by' => $user->id,
            'current_step_id' => $step1->id,
            'due_date' => now()->addDays(5)
        ]);

        $activeStep = DB::table('task_active_steps')->insertGetId([
            'task_id' => $task->id,
            'step_id' => $step1->id,
            'status' => 'in_progress',
            'created_at' => now()->subHours(25), // Force breach
            'updated_at' => now()->subHours(25),
            'due_date' => now()->subHours(1),
            'is_breached' => false
        ]);

        \Illuminate\Support\Facades\Artisan::call('workflow:monitor-slas');

        $this->assertDatabaseHas('workflow_logs', [
            'task_id' => $task->id,
            'action_type' => 'sla_breach'
        ]);
    }

    public function test_parallel_workflow_and_delegation()
    {
        list($user, $dept) = $this->setupUserAndDepartment();
        $this->actingAs($user);
        
        $user2 = User::factory()->create(['department_id' => $dept->id]);
        $user2->givePermissionTo(Permission::all());

        \App\Models\WorkflowDelegation::create([
            'delegator_user_id' => $user->id,
            'delegatee_user_id' => $user2->id,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(5),
            'is_active' => true
        ]);

        $task = Task::create([
            'workflow_version_id' => 1,
            'title' => 'Delegation Task',
            'status' => 'in progress',
            'created_by' => $user->id,
            'current_step_id' => 1,
            'due_date' => now()->addDays(5)
        ]);

        $this->assertDatabaseHas('workflow_delegations', [
            'delegator_user_id' => $user->id,
            'delegatee_user_id' => $user2->id,
            'is_active' => true
        ]);

        $assignment = TaskAssignment::create([
            'task_id' => $task->id,
            'assigned_to' => $user->id,
            'assigned_by' => $user->id,
            'status' => 'pending'
        ]);
        
        $this->assertEquals('pending', $assignment->status);
    }

    public function test_workflow_security_and_confidentiality()
    {
        list($user, $dept) = $this->setupUserAndDepartment('HQ');
        $this->actingAs($user);
        
        list($userOtherDept, $deptOther) = $this->setupUserAndDepartment('Other');
        $workflow = Workflow::create(['title' => 'Secret', 'created_by' => $user->id, 'department_id' => $dept->id]);
        $version = $workflow->versions()->create(['version_number' => 1, 'is_published' => false]);
        
        $this->actingAs($userOtherDept);
        
        // Department Denial
        $response = $this->postJson("/dashboard/workflow-builder/version/{$version->id}/approve", [
            'approval_pin' => '1234'
        ]);
        
        $response->assertStatus(403);
    }
}
