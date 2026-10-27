<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use App\Models\Groups;
use App\Models\categories;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Database\Seeders\RoleSeeder;

class Phase20TaskChatTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
        // Run roles seeder
        $this->seed(RoleSeeder::class);
    }

    /** @test */
    public function it_can_lazily_create_task_chat_group()
    {
        // 1. Setup User
        $user = User::factory()->create();
        $user->assignRole('super admin'); // Has necessary permissions

        // 2. Setup Task
        $task = new Task();
        $task->title = 'Test Task Chat';
        $task->status = 'pending';
        $task->created_by = $user->id;
        $task->due_date = now()->format('Y-m-d');
        $task->save();
        
        \App\Models\TaskAssignment::create([
            'task_id' => $task->id,
            'assigned_to' => $user->id,
            'assigned_by' => $user->id,
            'status' => 'pending'
        ]);

        $this->assertNull($task->chat_group_id);

        // 3. Hit Endpoint to lazy instantiate chat group
        $this->withoutExceptionHandling();
        $response = $this->actingAs($user)->postJson('/dashboard/task/' . $task->id . '/chat-group');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'chat_group_id',
            'group' => [
                'id',
                'name',
                'category_id'
            ]
        ]);

        // 4. Verify DB was updated
        $task->refresh();
        $this->assertNotNull($task->chat_group_id);
        
        $group = Groups::find($task->chat_group_id);
        $this->assertNotNull($group);
        $this->assertStringContainsString('Task #' . $task->id, $group->name);

        // Verify user was synced to group
        $this->assertTrue($group->users->contains($user->id));
        
        // Ensure category is Tasks
        $category = categories::find($group->category_id);
        $this->assertEquals('Tasks', $category->name);
    }
}
