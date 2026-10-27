<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Workflow;
use App\Models\Task;
use App\Models\User;
use App\Models\Posting;
use App\Events\WorkflowCompleted;
use Database\Seeders\Phase17Adm008Seeder;

class Phase17Adm008Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(Phase17Adm008Seeder::class);
    }

    public function test_adm008_redeployment()
    {
        $user = User::factory()->create();
        $workflow = Workflow::where('title', 'ADM-008 Redeployment')->first();
        
        $task = new Task();
        $task->title = 'ADM-008 Redeployment Test';
        $task->workflow_version_id = $workflow->versions()->first()->id;
        $task->due_date = '2026-10-01';
        $task->form_data = [
            'target_user_id' => $user->id,
            'mda_name' => 'Ministry of Defence',
            'assumption_date' => '2026-10-05'
        ];
        $task->status = 'completed';
        $task->created_by = $user->id;
        $task->save();

        event(new WorkflowCompleted($task));

        $posting = Posting::where('user_id', $user->id)->first();
        $this->assertEquals('Ministry of Defence', $posting->mda_name);
    }

    public function test_adm008_recall()
    {
        $user = User::factory()->create();
        $workflow = Workflow::where('title', 'ADM-008 Recall')->first();
        
        $task = new Task();
        $task->title = 'ADM-008 Recall Test';
        $task->workflow_version_id = $workflow->versions()->first()->id;
        $task->due_date = '2026-10-01';
        $task->form_data = [
            'target_user_id' => $user->id,
            'reason_for_recall' => 'End of tenure'
        ];
        $task->status = 'completed';
        $task->created_by = $user->id;
        $task->save();

        event(new WorkflowCompleted($task));

        $posting = Posting::where('user_id', $user->id)->first();
        $this->assertEquals('OAGF Headquarters Pool', $posting->mda_name);
    }

    public function test_adm008_replacement()
    {
        $incoming = User::factory()->create();
        $outgoing = User::factory()->create();
        $workflow = Workflow::where('title', 'ADM-008 Replacement')->first();
        
        $task = new Task();
        $task->title = 'ADM-008 Replacement Test';
        $task->workflow_version_id = $workflow->versions()->first()->id;
        $task->due_date = '2026-10-01';
        $task->form_data = [
            'target_user_id' => $incoming->id,
            'replaced_user_id' => $outgoing->id,
            'mda_name' => 'Ministry of Aviation',
            'assumption_date' => '2026-10-10'
        ];
        $task->status = 'completed';
        $task->created_by = $incoming->id;
        $task->save();

        event(new WorkflowCompleted($task));

        $postingIncoming = Posting::where('user_id', $incoming->id)->first();
        $this->assertEquals('Ministry of Aviation', $postingIncoming->mda_name);

        $postingOutgoing = Posting::where('user_id', $outgoing->id)->first();
        $this->assertEquals('OAGF Headquarters Pool', $postingOutgoing->mda_name);
    }
}
