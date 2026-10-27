<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use App\Models\TaskAssignment;
use App\Models\WorkflowVersion;
use App\Models\WorkflowStep;
use App\Models\WorkflowTransition;
use Illuminate\Support\Facades\Notification;
use App\Notifications\WorkflowEventNotification;
use App\Services\GenericWorkflowService;

class WorkflowNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock a user
        $this->user = User::factory()->create();
        
        // Setup minimal workflow
        $this->version = WorkflowVersion::create([
            'workflow_id' => 1,
            'version_number' => '1.0',
            'is_published' => true
        ]);

        $this->step1 = WorkflowStep::create([
            'workflow_version_id' => $this->version->id,
            'name' => 'Start',
            'is_start_step' => true
        ]);
        
        $this->step2 = WorkflowStep::create([
            'workflow_version_id' => $this->version->id,
            'name' => 'Review',
            'is_end_step' => true
        ]);
        
        $this->transition = WorkflowTransition::create([
            'workflow_version_id' => $this->version->id,
            'from_step_id' => $this->step1->id,
            'to_step_id' => $this->step2->id,
            'action_name' => 'Submit for Approval'
        ]);
    }

    public function test_workflow_start_triggers_assignment_notification()
    {
        Notification::fake();

        $task = Task::create([
            'workflow_version_id' => $this->version->id,
            'title' => 'Test Task',
            'created_by' => $this->user->id,
            'status' => 'pending',
            'due_date' => now()->addDays(5)->format('Y-m-d')
        ]);

        $service = new GenericWorkflowService();
        $assignee = User::factory()->create();
        
        $service->start($task, $this->user, [$assignee->id]);

        Notification::assertSentTo(
            $assignee,
            WorkflowEventNotification::class,
            function ($notification, $channels) use ($assignee) {
                return $notification->toArray($assignee)['event_type'] === 'assigned' &&
                       in_array('mail', $channels) && 
                       in_array('database', $channels);
            }
        );
    }
}
