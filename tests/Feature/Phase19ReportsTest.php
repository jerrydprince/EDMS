<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use App\Models\Department;
use App\Models\Mail;
use App\Models\FileMovement;
use App\Models\Posting;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class Phase19ReportsTest extends TestCase
{
    // Removing RefreshDatabase since edms_test is out of sync with migration definitions.
    // We will clean up our test data manually or run within a transaction if needed.

    public function test_reports_analytics_endpoint()
    {
        $this->withoutExceptionHandling();

        // 1. Create a user
        $user = User::factory()->create();

        // 2. Give permissions and roles
        $permission = Permission::firstOrCreate(['name' => 'edms view']);
        $user->givePermissionTo($permission);
        
        $role = Role::firstOrCreate(['name' => 'super admin']);
        $user->assignRole($role);

        // 3. Create dummy data
        $dept = new Department();
        $dept->title = 'Test Dept ' . uniqid();
        $dept->save();
        
        $task = new Task();
        $task->title = 'Test Report Task';
        $task->status = 'completed';
        $task->assigned_by_department = $dept->id;
        $task->created_by = $user->id;
        $task->due_date = now()->format('Y-m-d');
        $task->save();

        \App\Models\TaskAssignment::create([
            'task_id' => $task->id,
            'assigned_to' => $user->id,
            'assigned_by' => $user->id,
        ]);

        $mail = new Mail();
        $mail->subject = 'Test Mail';
        $mail->body = 'Test Body';
        $mail->user_id = $user->id;
        $mail->recipient = $user->id;
        $mail->sender = $user->id;
        $mail->sender_id = $user->id;
        $mail->save();

        // 4. Hit Endpoint
        $response = $this->actingAs($user)->get('/edms/reports/analytics?dateFilter=30');

        $response->assertStatus(200);

        // 5. Assert Structure
        $response->assertJsonStructure([
            'workflowStatus',
            'registryTrends',
            'fileTrackingTrends',
            'departmentPerformance',
            'slaData',
            'welfare' => [
                'pool_deployments',
                'leave_requests'
            ]
        ]);
        
        // Assert that the 'completed' task exists in the response
        $this->assertArrayHasKey('completed', $response->json('workflowStatus'));
        
        // Cleanup (manually if RefreshDatabase is disabled)
        $task->assignments()->delete();
        $task->delete();
        $mail->delete();
        $user->delete();
        $dept->delete();
    }
}
