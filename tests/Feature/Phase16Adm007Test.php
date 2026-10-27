<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Workflow;
use App\Models\Task;
use App\Models\User;
use App\Models\Posting;
use Illuminate\Support\Facades\Event;
use App\Events\WorkflowCompleted;
use Database\Seeders\Phase16Adm007Seeder;

class Phase16Adm007Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(Phase16Adm007Seeder::class);
    }

    public function test_adm007_workflow_is_seeded()
    {
        $workflow = Workflow::where('title', 'ADM-007 Pool Posting')->first();
        $this->assertNotNull($workflow);
        $this->assertCount(3, $workflow->versions()->first()->steps);
    }

    public function test_posting_record_is_created_on_workflow_completion()
    {
        $user = User::factory()->create(['department_id' => 1]); // Primary admin department

        $workflow = Workflow::where('title', 'ADM-007 Pool Posting')->first();
        
        $task = new Task();
        $task->title = 'ADM-007 Pool Posting Test';
        $task->workflow_version_id = $workflow->versions()->first()->id;
        $task->due_date = '2026-10-01';
        $task->form_data = [
            'target_user_id' => $user->id,
            'mda_name' => 'Ministry of Health',
            'assumption_date' => '2026-10-01'
        ];
        $task->status = 'completed';
        $task->created_by = $user->id;
        $task->save();

        // Manually trigger the event that GenericWorkflowService would dispatch
        event(new WorkflowCompleted($task));

        // Assert posting record is created
        $posting = Posting::where('user_id', $user->id)->first();
        
        $this->assertNotNull($posting);
        $this->assertEquals('Ministry of Health', $posting->mda_name);
        $this->assertEquals('2026-10-01', $posting->assumption_date);
        
        // Assert the user's primary department_id is NOT mutated (Admin retains jurisdiction)
        $user->refresh();
        $this->assertEquals(1, $user->department_id);
    }
}
