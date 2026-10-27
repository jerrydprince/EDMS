<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use App\Models\WorkflowVersion;
use App\Models\WorkflowStep;
use App\Models\WorkflowSla;
use App\Models\TaskActiveStep;
use App\Models\WorkflowWorkingDay;
use App\Models\TaskAssignment;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;

class WorkflowSlaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'super-admin']);
        Role::firstOrCreate(['name' => 'director']);
    }

    public function test_sla_breach_detection_and_escalation()
    {
        // Set up SLA rules
        $user = User::factory()->create();
        $workflow = \App\Models\Workflow::create([
            'title' => 'SLA Workflow',
            'created_by' => $user->id
        ]);
        $version = WorkflowVersion::create([
            'workflow_id' => $workflow->id,
            'version_number' => 1,
            'is_published' => true
        ]);
        $step = WorkflowStep::create([
            'workflow_version_id' => $version->id,
            'name' => 'SLA Step',
            'is_start_step' => true,
            'is_end_step' => false
        ]);
        $sla = WorkflowSla::create([
            'workflow_step_id' => $step->id,
            'duration_hours' => 24,
            'warning_hours' => 2,
            'escalation_action' => 'reassign',
            'escalation_role' => 'director',
            'remove_original_assignee' => true,
            'is_working_hours_only' => false,
        ]);

        $task = new Task();
        $task->title = 'Test';
        $task->status = 'pending';
        $task->due_date = now();
        $task->created_by = $user->id;
        $task->workflow_version_id = $version->id;
        $task->save();
        
        // Create an active step that is already breached (due date in the past)
        $activeStep = TaskActiveStep::create([
            'task_id' => $task->id,
            'step_id' => $step->id,
            'status' => 'in_progress',
            'is_paused' => false,
            'due_date' => now()->subHours(1),
            'is_breached' => false
        ]);

        // Original assignee
        $originalAssignee = User::factory()->create();
        TaskAssignment::create([
            'task_id' => $task->id,
            'assigned_to' => $originalAssignee->id,
            'assigned_by' => $user->id,
            'status' => 'pending'
        ]);

        // Director user for escalation
        $director = User::factory()->create();
        $director->assignRole('director');

        // Run the console command
        Artisan::call('workflow:monitor-slas');

        // Assert breach was flagged
        $this->assertDatabaseHas('task_active_steps', [
            'id' => $activeStep->id,
            'is_breached' => true
        ]);

        // Assert original assignee was removed
        $this->assertDatabaseHas('task_assignments', [
            'task_id' => $task->id,
            'assigned_to' => $originalAssignee->id,
            'status' => 'uncompleted'
        ]);

        // Assert director was assigned
        $this->assertDatabaseHas('task_assignments', [
            'task_id' => $task->id,
            'assigned_to' => $director->id,
            'status' => 'pending'
        ]);
    }

    public function test_calculate_due_date_with_working_hours()
    {
        $service = app(\App\Services\GenericWorkflowService::class);
        
        // Setup working days (Mon-Fri 08:00 - 17:00)
        for ($i = 1; $i <= 5; $i++) {
            WorkflowWorkingDay::create([
                'day_of_week' => $i,
                'is_working_day' => true,
                'start_time' => '08:00:00',
                'end_time' => '17:00:00'
            ]);
        }
        
        $sla = new WorkflowSla(['duration_hours' => 10, 'is_working_hours_only' => true]);

        // Start on Monday 2026-09-14 15:00
        $startTime = Carbon::create(2026, 9, 14, 15, 0, 0);
        
        // 10 hours should take us to Tuesday 2026-09-15 16:00 (2 hours on Mon + 8 hours on Tue)
        // Mon 15:00 to 17:00 = 2 hours. Remaining 8 hours.
        // Tue 08:00 + 8 hours = 16:00.
        $expectedDueDate = Carbon::create(2026, 9, 15, 16, 0, 0);

        $dueDate = $service->calculateDueDate($startTime, $sla);

        $this->assertEquals($expectedDueDate->format('Y-m-d H:i'), $dueDate->format('Y-m-d H:i'));
    }
}
