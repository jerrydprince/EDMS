<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use App\Models\WorkflowVersion;
use App\Models\WorkflowStep;
use App\Models\WorkflowTransition;
use App\Models\TaskActiveStep;
use App\Models\TaskAssignment;
use Spatie\Permission\Models\Role;

class GenericWorkflowSecurityTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
        
        // Seed some basic generic workflow data for tests
        $this->workflowVersion = WorkflowVersion::create([
            'workflow_id' => 1, // dummy
            'version_number' => 1,
            'is_published' => true,
        ]);

        $this->step1 = WorkflowStep::create([
            'workflow_version_id' => $this->workflowVersion->id,
            'name' => 'Review',
            'step_type' => 'review',
        ]);
        
        $this->step2 = WorkflowStep::create([
            'workflow_version_id' => $this->workflowVersion->id,
            'name' => 'Approval',
            'step_type' => 'approval',
        ]);

        $this->transition = WorkflowTransition::create([
            'workflow_version_id' => $this->workflowVersion->id,
            'from_step_id' => $this->step1->id,
            'to_step_id' => $this->step2->id,
            'action_name' => 'Approve',
        ]);
        $this->initiator = User::factory()->create();
        $this->assignedUser = User::factory()->create();
        $this->unauthorizedUser = User::factory()->create();
        
        $permissionEdmsView = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'edms view']);
        $permissionEdmsUpdate = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'edms update']);
        $permissionWorkflowView = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'workflow view']);
        $permissionWorkflowCreate = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'workflow create']);
        
        $this->initiator->givePermissionTo([$permissionEdmsView, $permissionEdmsUpdate, $permissionWorkflowView, $permissionWorkflowCreate]);
        $this->assignedUser->givePermissionTo([$permissionEdmsView, $permissionEdmsUpdate, $permissionWorkflowView, $permissionWorkflowCreate]);
        $this->unauthorizedUser->givePermissionTo([$permissionEdmsView, $permissionEdmsUpdate, $permissionWorkflowView, $permissionWorkflowCreate]);

        $this->task = new Task();
        $this->task->title = 'Test Security Task';
        $this->task->workflow_version_id = $this->workflowVersion->id;
        $this->task->created_by = $this->initiator->id;
        $this->task->status = 'pending';
        $this->task->due_date = now();
        $this->task->save();

        TaskActiveStep::create([
            'task_id' => $this->task->id,
            'step_id' => $this->step1->id,
            'status' => 'in_progress',
            'due_date' => now()->addDays(2),
        ]);

        TaskAssignment::create([
            'task_id' => $this->task->id,
            'assigned_to' => $this->assignedUser->id,
            'assigned_by' => $this->initiator->id,
            'status' => 'pending'
        ]);
    }

    public function test_unauthorized_user_cannot_execute_transition()
    {
        $this->withoutExceptionHandling();
        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);

        $response = $this->actingAs($this->unauthorizedUser)
            ->postJson("/dashboard/generic-workflow/instances/{$this->task->id}/execute", [
                'transition_ids' => [$this->transition->id],
            ]);
    }

    public function test_authorized_user_can_execute_transition()
    {
        $this->withExceptionHandling();
        $response = $this->actingAs($this->assignedUser)
            ->postJson("/dashboard/generic-workflow/instances/{$this->task->id}/execute", [
                'transition_ids' => [$this->transition->id],
            ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Action executed successfully.']);
    }

    public function test_transition_validation_errors()
    {
        $this->withExceptionHandling();
        $response = $this->actingAs($this->assignedUser)
            ->postJson("/dashboard/generic-workflow/instances/{$this->task->id}/execute", []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['transition_ids']);
    }
}
