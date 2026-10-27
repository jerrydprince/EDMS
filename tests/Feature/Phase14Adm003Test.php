<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Task;
use App\Models\Workflow;
use App\Models\Department;
use App\Models\User;
use Database\Seeders\Phase14Adm003Seeder;

class Phase14Adm003Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed department
        $dept = Department::forceCreate(['title' => 'Administration']);
        
        // Run seeder
        $this->seed(Phase14Adm003Seeder::class);
    }

    public function test_adm003_workflows_seeded_with_schemas()
    {
        $workflows = Workflow::where('title', 'like', 'ADM-003%')->get();
        $this->assertCount(5, $workflows);

        $aperWorkflow = Workflow::where('title', 'ADM-003 APER Workflow')->first();
        $this->assertNotNull($aperWorkflow);
        
        $version = $aperWorkflow->versions()->first();
        $this->assertNotNull($version->form_schema);
        $this->assertIsArray($version->form_schema);
        $this->assertEquals('reporting_year', $version->form_schema[0]['name']);
    }

    public function test_task_can_store_form_data()
    {
        $user = User::factory()->create();

        $task = Task::create([
            'title' => 'Test Task',
            'due_date' => now()->addDays(5)->toDateString(),
            'priority' => '1',
            'form_data' => [
                'reporting_year' => 2024,
                'self_assessment' => 'Did great this year',
            ],
            'created_by' => $user->id
        ]);

        $task->refresh();

        $this->assertIsArray($task->form_data);
        $this->assertEquals(2024, $task->form_data['reporting_year']);
        $this->assertEquals('Did great this year', $task->form_data['self_assessment']);
    }
}
