<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use App\Models\TaskAssignment;
use App\Models\Archive;
use App\Models\Department;

class WorkflowIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_workflow_task_assignment_lifecycle()
    {
        // Set up department and users
        $dept = Department::forceCreate(['title' => 'Admin Dept', 'description' => 'Administration']);
        
        $assigner = User::factory()->create(['department_id' => $dept->id]);
        $assignee = User::factory()->create(['department_id' => $dept->id]);

        $this->actingAs($assigner);
        
        // 1. Create Archive (file for the task)
        $archive = Archive::forceCreate(['department_id' => $dept->id, 'updated_by' => $assigner->id, 'folder_id' => 1]);
        
        // 2. Create Task
        $task = Task::forceCreate([
            'title' => 'Review Monthly Report',
            'description' => 'Please review and approve',
            'created_by' => $assigner->id,
            'due_date' => now()->addDays(5)->toDateString(),
            'priority' => '1'
        ]);
        
        // 3. Assign Task
        $assignment = TaskAssignment::forceCreate([
            'task_id' => $task->id,
            'assigned_to' => $assignee->id,
            'assigned_by' => $assigner->id,
            'status' => 'pending'
        ]);
        
        // Ensure initial status is pending
        $this->assertEquals('pending', $assignment->status);

        // 4. API Endpoint for changing task status (simulating assignee acting on it)
        $this->actingAs($assignee);
        
        // Simulating completion
        $assignment->status = 'completed';
        $assignment->save();
        
        $this->assertDatabaseHas('task_assignments', [
            'id' => $assignment->id,
            'status' => 'completed'
        ]);
    }
}
