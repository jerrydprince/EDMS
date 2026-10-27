<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use App\Models\TaskAssignment;
use App\Models\TaskDocumentVersion;
use App\Models\TaskMinute;
use App\Models\TaskAnnotation;
use App\Services\ChainResolver;
use App\Services\DocumentSigningService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class StreamlinedWorkflowVerificationTest extends TestCase
{
    use \Illuminate\Foundation\Testing\DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_complete_streamlined_workflow_chain()
    {
        // 1. Load users
        $staff = User::find(43);      // Joy Abiodun (regular user)
        $ad = User::find(42);         // Ismail Adesina (assistant director)
        $dd = User::find(41);         // Favour Uche (deputy director)
        $director = User::find(40);   // Hamza Maigari (director)
        $agf = User::find(38);        // Joseph Okorie (super user)

        $this->assertNotNull($staff);
        $this->assertNotNull($ad);
        $this->assertNotNull($dd);
        $this->assertNotNull($director);
        $this->assertNotNull($agf);

        // 2. Ensure test task exists (use Task 7 or create temporary test task)
        $task = Task::find(7);
        $this->assertNotNull($task);

        // Test ChainResolver routing
        $this->assertEquals($ad->id, ChainResolver::resolveNextApprover($staff, $task)->id);
        $this->assertEquals($dd->id, ChainResolver::resolveNextApprover($ad, $task)->id);
        $this->assertEquals($director->id, ChainResolver::resolveNextApprover($dd, $task)->id);
        $this->assertEquals($agf->id, ChainResolver::resolveNextApprover($director, $task)->id);
        $this->assertNull(ChainResolver::resolveNextApprover($agf, $task));

        // Test Skip targets
        $adSkip = ChainResolver::getEligibleSkipTargets($ad, $task);
        $this->assertNotEmpty($adSkip);
        $this->assertEquals($director->id, $adSkip[0]['id']);

        $ddSkip = ChainResolver::getEligibleSkipTargets($dd, $task);
        $this->assertNotEmpty($ddSkip);
        $this->assertEquals($agf->id, $ddSkip[0]['id']);

        // Test Leave auto-skip
        $ad->is_on_leave = true;
        $ad->save();
        $this->assertEquals($dd->id, ChainResolver::resolveNextApprover($staff, $task)->id);
        $ad->is_on_leave = false;
        $ad->save();

        echo "\n[PASS] ChainResolver and Leave auto-skip verified.\n";
    }

    public function test_signing_service_and_endpoints()
    {
        $task = Task::find(7);
        $staff = User::find(43);
        $ad = User::find(42);
        $director = User::find(40);
        $agf = User::find(38);

        // A. Test chain-info endpoint
        $this->actingAs($ad);
        $response = $this->getJson("/dashboard/tasks/{$task->id}/chain-info");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'current_user',
            'next_approver',
            'skip_targets',
            'return_target'
        ]);
        $this->assertEquals($director->id, $response->json('skip_targets.0.id'));

        // B. Test PIN verification failure
        $failResponse = $this->postJson("/dashboard/tasks/{$task->id}/sign-forward", [
            'pin' => 'WRONG_PIN',
            'remark' => 'Test invalid pin'
        ]);
        $failResponse->assertStatus(422);

        // C. Test Skip level with reason via AD
        $skipResponse = $this->postJson("/dashboard/tasks/{$task->id}/sign-forward", [
            'pin' => '08283139',
            'remark' => 'Endorsed for director clearance',
            'skip_target_id' => $director->id,
            'skip_reason' => 'Emergency urgent review required by Director'
        ]);
        $skipResponse->assertStatus(200);
        $this->assertTrue($skipResponse->json('success'));

        // Verify Minute row recorded skip reason
        $latestMinute = TaskMinute::where('task_id', $task->id)->latest('id')->first();
        $this->assertEquals('Emergency urgent review required by Director', $latestMinute->skip_reason);
        $this->assertEquals('Assistant Director', $latestMinute->role);

        // D. Test Return with notes by Director
        $this->actingAs($director);
        $returnResponse = $this->postJson("/dashboard/tasks/{$task->id}/return", [
            'reason' => 'Please adjust page 1 formatting and recalculate figure.',
            'target_user_id' => $ad->id,
            'annotations' => [
                ['page' => 1, 'x' => 120.5, 'y' => 200.0, 'text' => 'Recalculate total budget']
            ]
        ]);
        $returnResponse->assertStatus(200);
        $this->assertTrue($returnResponse->json('success'));

        // Verify annotation stored
        $latestAnnotation = TaskAnnotation::latest('id')->first();
        $this->assertEquals('Recalculate total budget', $latestAnnotation->text);
        $this->assertNull($latestAnnotation->resolved_at);

        // Test Annotation resolve
        $resolveRes = $this->postJson("/dashboard/task-annotations/{$latestAnnotation->id}/resolve");
        $resolveRes->assertStatus(200);
        $latestAnnotation->refresh();
        $this->assertNotNull($latestAnnotation->resolved_at);

        // E. Test Compulsory Signature for Director & AGF
        // Sign as Director
        $dirSignRes = $this->postJson("/dashboard/tasks/{$task->id}/sign-forward", [
            'pin' => '08283139',
            'remark' => 'Director approval and endorsement.'
        ]);
        $dirSignRes->assertStatus(200);

        // Sign & Final Approve as AGF
        $this->actingAs($agf);
        $agfSignRes = $this->postJson("/dashboard/tasks/{$task->id}/sign-forward", [
            'pin' => '08283139',
            'remark' => 'Final Executive Clearance granted.'
        ]);
        $agfSignRes->assertStatus(200);

        // Check task is completed and signed
        $task->refresh();
        $this->assertEquals('completed', $task->status);
        $this->assertEquals(1, $task->is_signed);

        echo "\n[PASS] End-to-end signing, return notes, skip reasons, compulsory signatures, and final AGF approval verified successfully.\n";
    }

    public function test_separate_approve_and_optional_attachments()
    {
        $this->withoutExceptionHandling();
        $task = Task::find(7);
        $ad = User::find(42);
        $staff = User::find(43);

        // 1. Create a subordinate assignment under review
        $assignment = TaskAssignment::create([
            'task_id' => $task->id,
            'assigned_to' => $staff->id,
            'assigned_by' => $ad->id,
            'status' => 'under review',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $task->update(['status' => 'under review']);

        // 2. AD approves subordinate submission without forwarding upward
        $this->actingAs($ad);
        $approveRes = $this->postJson("/dashboard/tasks/{$task->id}/approve-submission", [
            'remark' => 'Subordinate submission approved and checked.',
            'pin' => '08283139',
        ]);

        $approveRes->assertStatus(200);
        $this->assertTrue($approveRes->json('success'));

        $assignment->refresh();
        $this->assertEquals('completed', $assignment->status);
        $task->refresh();
        $this->assertEquals('in progress', $task->status);

        // 3. Test submitting to assigner with optional attachments
        $tempAttachment = \Illuminate\Http\UploadedFile::fake()->create('supporting_memo.pdf', 150, 'application/pdf');

        $submitRes = $this->postJson("/dashboard/tasks/{$task->id}/sign-forward", [
            'pin' => '08283139',
            'remark' => 'Approved and submitted to assigner with supporting memo.',
            'attachments' => [$tempAttachment],
        ]);

        if ($submitRes->status() !== 200) {
            fwrite(STDERR, "\nERROR RESPONSE: " . $submitRes->getContent() . "\n");
        }

        $submitRes->assertStatus(200);
        $this->assertTrue($submitRes->json('success'));

        // Verify attachment created as TaskDoc
        $taskDoc = \App\Models\TaskDoc::where('task_id', $task->id)
            ->where('file_name', 'supporting_memo.pdf')
            ->first();
        $this->assertNotNull($taskDoc);

        echo "\n[PASS] Separate Approve action and optional attachments verified successfully.\n";
    }

    public function test_upward_submission_hierarchy_for_deputy_director()
    {
        $task = Task::find(7);
        $ad = User::find(42);         // Ismail Adesina (assistant director)
        $dd = User::find(41);         // Favour Uche (deputy director)
        $director = User::find(40);   // Hamza Maigari (director)

        // 1. Acting as Deputy Director (Favour Uche) on task 7 where AD submitted upward
        $this->actingAs($dd);
        $chainRes = $this->getJson("/dashboard/tasks/{$task->id}/chain-info");
        $chainRes->assertStatus(200);

        // Next approver MUST be Director (Hamza Maigari 40), NEVER subordinate AD (Ismail Adesina 42)
        $this->assertEquals($director->id, $chainRes->json('next_approver.id'));
        $this->assertEquals('Director', $chainRes->json('next_approver.role'));

        // Return target MUST be Assistant Director (Ismail Adesina 42)
        $this->assertEquals($ad->id, $chainRes->json('return_target.id'));
        $this->assertEquals('Assistant Director', $chainRes->json('return_target.role'));

        // 2. Deputy Director signs and submits to assigner (forwarding upward)
        $submitRes = $this->postJson("/dashboard/tasks/{$task->id}/sign-forward", [
            'pin' => '08283139',
            'remark' => 'Reviewed and recommended for Director clearance.',
        ]);

        if ($submitRes->status() !== 200) {
            fwrite(STDERR, "\nSUBMIT ERROR: " . $submitRes->getContent() . "\n");
        }

        $submitRes->assertStatus(200);
        $this->assertTrue($submitRes->json('success'));

        // 3. Verify latest assignment is forwarded UPWARD to Director (40)
        $latestAssignment = TaskAssignment::where('task_id', $task->id)->latest('id')->first();
        $this->assertEquals($director->id, $latestAssignment->assigned_to);
        $this->assertEquals($dd->id, $latestAssignment->assigned_by);
        $this->assertEquals('under review', $latestAssignment->status);

        echo "\n[PASS] Upward submission to Director and downward return target verified successfully.\n";
    }
}

