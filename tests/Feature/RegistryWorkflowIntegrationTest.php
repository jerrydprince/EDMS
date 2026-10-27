<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\File;
use App\Models\Mail;
use App\Models\Workflow;
use App\Models\WorkflowVersion;
use App\Models\WorkflowStep;
use App\Models\Task;
use App\Models\FileMovement;
use Illuminate\Support\Facades\DB;

class RegistryWorkflowIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Database\Eloquent\Model::unguard();
        $this->withoutMiddleware();

        // Create standard workflows
        $admin = User::factory()->create();
        $workflow = Workflow::create(['title' => 'Registry Incoming', 'created_by' => $admin->id]);
        $version = WorkflowVersion::create([
            'workflow_id' => $workflow->id,
            'version_number' => 1,
            'is_published' => true
        ]);
        WorkflowStep::create(['workflow_version_id' => $version->id, 'name' => 'Start', 'step_type' => 'start', 'is_start_step' => true]);

        $mailWorkflow = Workflow::create(['title' => 'Mail Incoming', 'created_by' => $admin->id]);
        $mailVersion = WorkflowVersion::create([
            'workflow_id' => $mailWorkflow->id,
            'version_number' => 1,
            'is_published' => true
        ]);
        WorkflowStep::create(['workflow_version_id' => $mailVersion->id, 'name' => 'Start', 'step_type' => 'start', 'is_start_step' => true]);
    }

    public function test_submit_mail_to_workflow()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $mail = Mail::forceCreate([
            'subject' => 'Test Subject',
            'body' => 'Test Body',
            'sender' => 'Sender',
            'sender_id' => $user->id,
            'recipient' => 'Receiver',
            'user_id' => $user->id
        ]);

        $response = $this->post("/dashboard/mails/submit-workflow/{$mail->id}");
        $response->assertStatus(302);
        
        $this->assertDatabaseHas('task', [
            'mail_id' => $mail->id,
            'title' => 'Incoming Mail: Test Subject'
        ]);
        $this->assertDatabaseHas('task_active_steps', []);
    }

    public function test_jacket_mail_as_file_and_submit()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $mail = Mail::forceCreate([
            'subject' => 'Test File Jacketing',
            'body' => 'Test Body',
            'sender' => 'Sender',
            'sender_id' => $user->id,
            'recipient' => 'Receiver',
            'user_id' => $user->id
        ]);

        $response = $this->post("/dashboard/mails/jacket-file-submit/{$mail->id}");
        $response->assertStatus(302);

        $this->assertDatabaseHas('files', [
            'name' => 'Test File Jacketing'
        ]);

        $file = File::where('name', 'Test File Jacketing')->first();
        
        $this->assertDatabaseHas('task', [
            'file_id' => $file->id,
            'title' => 'Registry File: Test File Jacketing'
        ]);
    }

    public function test_workflow_transition_creates_file_movement()
    {
        $user1 = User::factory()->create(['unit_id' => 1]);
        $user2 = User::factory()->create(['unit_id' => 2]);
        
        $file = File::forceCreate([
            'name' => 'Test File',
            'file_type' => 'External Mail',
            'doc_no' => '123',
            'created_by' => $user1->id
        ]);

        $task = Task::create([
            'title' => 'Registry Task',
            'file_id' => $file->id,
            'created_by' => $user1->id,
            'due_date' => now()
        ]);

        $workflowService = app(\App\Services\GenericWorkflowService::class);
        
        // Mocking the transition
        $version = WorkflowVersion::where('version_number', 1)->first();
        $startStep = $version->steps()->first();
        $nextStep = WorkflowStep::create(['workflow_version_id' => $version->id, 'name' => 'Next Step', 'step_type' => 'process']);
        
        $transition = \App\Models\WorkflowTransition::create([
            'from_step_id' => $startStep->id,
            'to_step_id' => $nextStep->id,
            'action_name' => 'Approve'
        ]);

        DB::table('task_active_steps')->insert([
            'task_id' => $task->id,
            'step_id' => $startStep->id,
            'status' => 'in_progress',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $workflowService->executeAction($task, $user1, $transition, [$user2->id]);

        $this->assertDatabaseHas('file_movements', [
            'file_id' => $file->id,
            'sent_from' => 1, // unit_id 1
            'sent_to' => 2    // unit_id 2
        ]);
    }

    public function test_user_can_acknowledge_file_movement()
    {
        $sender = User::factory()->create(['unit_id' => 1]);
        $receiver = User::factory()->create(['unit_id' => 2]);

        $file = File::forceCreate([
            'name' => 'Physical File',
            'file_type' => 'Internal',
            'doc_no' => '124',
            'created_by' => $sender->id
        ]);

        $task = Task::create([
            'title' => 'Registry Task 2',
            'file_id' => $file->id,
            'created_by' => $sender->id,
            'due_date' => now()
        ]);

        $movement = FileMovement::create([
            'file_id' => $file->id,
            'sent_from' => 1,
            'sent_to' => 2,
            'sent_to_branch' => 'unit',
            'sent_to_unit' => 2,
            'status' => 'pending',
            'file_movement_id' => time()
        ]);

        $this->actingAs($receiver);
        $response = $this->post("/dashboard/registry/movements/{$movement->id}/acknowledge");
        $response->assertStatus(200);

        $this->assertDatabaseHas('file_movements', [
            'id' => $movement->id,
            'status' => 'delivered'
        ]);

        $this->assertDatabaseHas('workflow_logs', [
            'task_id' => $task->id,
            'action_type' => 'file_received',
            'action_by' => $receiver->id
        ]);
    }

    public function test_user_cannot_acknowledge_unauthorized_movement()
    {
        $sender = User::factory()->create(['unit_id' => 1]);
        $unauthorizedUser = User::factory()->create(['unit_id' => 3]); // Different unit
        
        $file = File::forceCreate([
            'name' => 'Physical File 2',
            'file_type' => 'Internal',
            'doc_no' => '125',
            'created_by' => $sender->id
        ]);

        $movement = FileMovement::create([
            'file_id' => $file->id,
            'sent_from' => $sender->id,
            'sent_to' => 2, // arbitrary user ID
            'sent_to_branch' => 'unit',
            'sent_to_unit' => 2, // It was sent to unit 2
            'status' => 'pending',
            'file_movement_id' => time()
        ]);

        $this->actingAs($unauthorizedUser);
        
        $response = $this->post("/dashboard/registry/movements/{$movement->id}/acknowledge");
        
        $response->assertStatus(403);
        $this->assertEquals('pending', $movement->fresh()->status);
    }
}
