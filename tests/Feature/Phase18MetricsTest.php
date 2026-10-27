<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use App\Models\Role;

class Phase18MetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_metrics_endpoint_scope()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        
        // Let's create a pending task
        $task = new Task();
        $task->title = 'Test Task';
        $task->status = 'pending';
        $task->created_by = $user->id;
        $task->due_date = now()->format('Y-m-d');
        $task->save();

        \App\Models\TaskAssignment::create([
            'task_id' => $task->id,
            'assigned_to' => $user->id,
            'assigned_by' => $user->id,
        ]);

        $permission = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'edms view']);
        $user->givePermissionTo($permission);

        $response = $this->actingAs($user)->get('/edms/metrics');
        
        $response->assertStatus(200);
        $response->assertJson([
            'pending' => 1,
            'completed' => 0
        ]);
    }
}
