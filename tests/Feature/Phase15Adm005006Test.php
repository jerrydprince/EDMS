<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use App\Models\WorkflowSla;
use Database\Seeders\Phase15Adm005And006Seeder;

class Phase15Adm005006Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(Phase15Adm005And006Seeder::class);
    }

    public function test_adm005_and_adm006_workflows_are_seeded()
    {
        $workflows = Workflow::where('title', 'like', 'ADM-005%')
            ->orWhere('title', 'like', 'ADM-006%')
            ->get();
            
        $this->assertCount(11, $workflows); // 5 for 005 and 6 for 006

        $loanWorkflow = Workflow::where('title', 'ADM-006 Staff Loans')->first();
        $this->assertNotNull($loanWorkflow);
        
        $version = $loanWorkflow->versions()->first();
        $this->assertNotNull($version->form_schema);
        $this->assertEquals('loan_type', $version->form_schema[0]['name']);
    }

    public function test_slas_are_configured_on_steps()
    {
        $loanWorkflow = Workflow::where('title', 'ADM-006 Staff Loans')->first();
        $version = $loanWorkflow->versions()->first();
        $steps = $version->steps;
        
        $this->assertCount(2, $steps);

        $step1 = $steps->where('name', 'HR Verification')->first();
        $this->assertNotNull($step1->slas);
        $this->assertCount(1, $step1->slas);

        $sla = $step1->slas->first();
        $this->assertEquals(48, $sla->duration_hours);
        $this->assertEquals('super admin', $sla->escalation_role);
    }
}
