<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Department;
use App\Models\Workflow;
use App\Models\WorkflowVersion;

class Phase12Adm002Test extends TestCase
{
    use RefreshDatabase;

    public function test_adm002_workflows_are_seeded()
    {
        $this->artisan('db:seed', ['--class' => 'Phase12Adm002Seeder']);

        $workflows = Workflow::where('title', 'like', 'ADM-002%')->get();
        $this->assertCount(9, $workflows);

        $adminDept = Department::where('title', 'Administration')->first();
        if ($adminDept) {
            foreach ($workflows as $wf) {
                $this->assertEquals($adminDept->id, $wf->department_id);
            }
        }
    }

    public function test_adm002_workflows_have_correct_structure()
    {
        $this->artisan('db:seed', ['--class' => 'Phase12Adm002Seeder']);

        $workflow = Workflow::where('title', 'ADM-002-3 Confirmation of Appointment')->first();
        $version = $workflow->versions()->first();

        // 8 steps
        $this->assertEquals(8, $version->steps()->count());
        // 7 transitions
        $this->assertEquals(7, $version->transitions()->count());
    }

    public function test_workflow_department_isolation_when_creating_step()
    {
        $this->withoutMiddleware();
        \Illuminate\Database\Eloquent\Model::unguard();
        
        $adminDept = Department::create(['title' => 'Administration']);
        $otherDept = Department::create(['title' => 'Other Dept']);

        $user = User::factory()->create(['department_id' => $adminDept->id]);
        
        $workflow = Workflow::create(['title' => 'Test Workflow', 'department_id' => $adminDept->id, 'created_by' => $user->id]);
        $version = WorkflowVersion::create(['workflow_id' => $workflow->id, 'version_number' => 1, 'is_published' => false]);

        $this->actingAs($user);

        // Attempt to create a step assigned to 'Other Dept'
        $response = $this->post("/dashboard/workflow-builder/version/{$version->id}/steps", [
            'name' => 'Review',
            'step_type' => 'review',
            'department_id' => $otherDept->id,
            'is_start_step' => false,
            'is_end_step' => false
        ]);

        $response->assertStatus(403);

        // Attempt to create a step assigned to 'Administration'
        $response = $this->post("/dashboard/workflow-builder/version/{$version->id}/steps", [
            'name' => 'Review',
            'step_type' => 'review',
            'department_id' => $adminDept->id,
            'is_start_step' => false,
            'is_end_step' => false
        ]);

        $response->assertStatus(302); // Redirect back on success
        $this->assertEquals(1, $version->steps()->count());
    }
}
