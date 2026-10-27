<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use App\Models\TaskAssignment;
use App\Models\TaskDoc;
use App\Models\Workflow;
use App\Models\WorkflowVersion;
use App\Models\WorkflowStep;
use App\Models\WorkflowTransition;
use App\Models\DocumentTemplate;
use App\Services\TemplateHydrationService;
use App\Services\GenericWorkflowService;
use Illuminate\Support\Facades\DB;

class MasterRoutingWorkflowHubAndSpokeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Database\Eloquent\Model::unguard();
    }

    /**
     * Phase 1 Test: Blueprint-to-Docket Linking & 6-Step Hub-and-Spoke Topology
     */
    public function test_phase1_master_routing_workflow_topology_exists()
    {
        $version = WorkflowVersion::whereHas('workflow', function($q) {
            $q->where('title', 'Master Routing Workflow');
        })->where('is_published', true)->first();

        $this->assertNotNull($version, 'Master Routing Workflow published version must exist');
        
        $steps = $version->steps()->get();
        $this->assertGreaterThanOrEqual(6, $steps->count(), 'Master Routing Workflow must have at least 6 steps');

        $transitions = $version->transitions()->get();
        $this->assertGreaterThanOrEqual(7, $transitions->count(), 'Master Routing Workflow must have at least 7 transitions');

        // Check for Step 1 (Registry), Step 2 (AGF Hub 1), Step 5 (AGF Hub 2), Step 6 (Dispatch)
        $this->assertTrue($steps->contains('name', 'Central Registry Intake & Registration'));
        $this->assertTrue($steps->contains('name', 'AGF Executive Direction & Minuting'));
        $this->assertTrue($steps->contains('name', 'Department Treatment & Directorate Review'));
        $this->assertTrue($steps->contains('name', 'AGF Final Approval & Decision'));
        $this->assertTrue($steps->contains('name', 'Central Registry Dispatch & Closure'));
    }

    /**
     * Phase 2 Test: AGF Executive Routing & Minute Logging
     */
    public function test_phase2_agf_executive_minuting_and_routing()
    {
        $agf = User::role('super user')->first() ?: User::first();
        $director = User::role('director')->first() ?: User::factory()->create();

        $task = Task::forceCreate([
            'title' => 'Test Inward Correspondence Docket',
            'created_by' => $agf->id,
            'status' => 'pending',
            'due_date' => now()->addDays(3)->format('Y-m-d')
        ]);

        $this->withoutExceptionHandling();
        $this->actingAs($agf);

        $response = $this->postJson('/dashboard/executive-route-task', [
            'task_id' => $task->id,
            'director_user_id' => $director->id,
            'directive' => 'For Urgent Technical Review and Recommendation'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify task assigned to director
        $this->assertDatabaseHas('task_assignments', [
            'task_id' => $task->id,
            'assigned_to' => $director->id,
            'status' => 'pending'
        ]);

        // Verify minute logged
        $this->assertDatabaseHas('task_minutes', [
            'task_id' => $task->id,
            'directive' => 'For Urgent Technical Review and Recommendation'
        ]);
    }

    /**
     * Phase 3 Test: Template Hydration Engine & Document Generation
     */
    public function test_phase3_template_hydration_and_tokens()
    {
        $templates = DocumentTemplate::all();
        $this->assertGreaterThanOrEqual(4, $templates->count(), 'At least 4 institutional templates must be seeded');

        $memoTemplate = DocumentTemplate::where('type', 'memo')->first();
        $this->assertNotNull($memoTemplate, 'Directorate memo template must exist');

        $dummyTask = Task::first() ?: Task::forceCreate(['title' => 'Hydration Unit Test', 'created_by' => 1]);
        $dummyUser = User::first();

        $hydrationService = app(TemplateHydrationService::class);
        $result = $hydrationService->hydrate($memoTemplate->content_html, $dummyTask, $dummyUser, [
            'custom_finding' => 'Financial regulations section 402 observed.'
        ]);

        $this->assertIsString($result);
        $this->assertStringNotContainsString('{{ tracking_id }}', $result);
        $this->assertStringNotContainsString('{{ title }}', $result);
    }

    /**
     * Phase 4 & 5 Test: AGF Final Approval Advances to Step 6 Dispatch & Generates Approval Instrument
     */
    public function test_phase4_and_5_agf_final_approval_and_dispatch_instrument()
    {
        $agf = User::role('super user')->first() ?: User::first();
        $agf->approval_pin = '1234';
        $agf->save();

        $version = WorkflowVersion::whereHas('workflow', function($q) {
            $q->where('title', 'Master Routing Workflow');
        })->where('is_published', true)->first();

        $step5 = WorkflowStep::where('workflow_version_id', $version->id)
            ->where('name', 'AGF Final Approval & Decision')
            ->first();

        $step6 = WorkflowStep::where('workflow_version_id', $version->id)
            ->where('name', 'Central Registry Dispatch & Closure')
            ->first();

        $task = Task::forceCreate([
            'title' => 'Docket Awaiting Final Apex Approval',
            'created_by' => $agf->id,
            'status' => 'under review',
            'workflow_version_id' => $version->id,
            'current_step_id' => $step5 ? $step5->id : null,
            'due_date' => now()->addDays(2)->format('Y-m-d')
        ]);

        // Attach existing valid PDF to task for DocumentSigningService
        $existingDoc = TaskDoc::where('path', 'like', '%.pdf')->latest('id')->first();
        if ($existingDoc) {
            TaskDoc::create([
                'task_id' => $task->id,
                'file_name' => 'Source Document.pdf',
                'path' => $existingDoc->path,
                'size' => $existingDoc->size,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if ($step5) {
            DB::table('task_active_steps')->insert([
                'task_id' => $task->id,
                'step_id' => $step5->id,
                'status' => 'in_progress',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        $this->actingAs($agf);

        $response = $this->postJson("/dashboard/tasks/{$task->id}/sign-forward", [
            'pin' => '1234',
            'remark' => 'Executive approval granted for immediate release.'
        ]);

        if ($response->status() !== 200) {
            dump($response->json());
        }

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify task reached Step 6 (Central Registry Dispatch & Closure) and completed
        $task->refresh();
        $this->assertEquals(1, $task->is_signed);
        if ($step6) {
            $this->assertEquals($step6->id, $task->current_step_id);
        }

        // Verify approval instrument PDF generated and attached
        $this->assertDatabaseHas('task_docs', [
            'task_id' => $task->id,
            'file_name' => 'Official AGF Executive Approval Instrument.pdf'
        ]);
    }
}
