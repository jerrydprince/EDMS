<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\WorkflowRule;
use Spatie\Permission\Models\Permission;

class WorkflowSettingsTest extends TestCase
{
    // Use DatabaseTransactions so we don't wipe the DB
    use \Illuminate\Foundation\Testing\DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure permissions exist
        Permission::firstOrCreate(['name' => 'branch setup view']);
        Permission::firstOrCreate(['name' => 'branch setup create']);
        Permission::firstOrCreate(['name' => 'branch setup delete']);
    }

    public function test_unauthorized_user_cannot_access_workflow_settings()
    {
        $user = User::factory()->create(); // standard user with no permissions

        $response = $this->actingAs($user)->get('/dashboard/workflow-settings');
        
        $response->assertStatus(403);
    }

    public function test_authorized_user_can_access_workflow_settings()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('branch setup view');

        $response = $this->actingAs($user)->get('/dashboard/workflow-settings');
        
        $response->assertStatus(200);
    }

    public function test_authorized_user_can_create_workflow_rule()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('branch setup create');

        $data = [
            'workflow_type' => 'Leave Request',
            'rule_key' => 'approval_threshold',
            'rule_value' => '10',
            'description' => 'Threshold test'
        ];

        $response = $this->actingAs($user)->post('/dashboard/workflow-settings', $data);
        
        $response->assertStatus(302); // Redirect back
        $this->assertDatabaseHas('workflow_rules', $data);
    }

    public function test_validation_prevents_empty_rule_creation()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('branch setup create');

        $data = [
            'workflow_type' => '',
            'rule_key' => '',
        ];

        $response = $this->actingAs($user)->post('/dashboard/workflow-settings', $data);
        
        $response->assertSessionHasErrors(['workflow_type', 'rule_key', 'rule_value']);
    }

    public function test_authorized_user_can_update_workflow_rule()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('branch setup create');

        $rule = WorkflowRule::create([
            'workflow_type' => 'Leave Request',
            'rule_key' => 'approval_threshold',
            'rule_value' => '5',
            'description' => 'Original'
        ]);

        $updateData = [
            'workflow_type' => 'Leave Request',
            'rule_key' => 'approval_threshold',
            'rule_value' => '20',
            'description' => 'Updated'
        ];

        $response = $this->actingAs($user)->post("/dashboard/workflow-settings/{$rule->id}", $updateData);
        
        $response->assertStatus(302);
        $this->assertDatabaseHas('workflow_rules', ['rule_value' => '20', 'description' => 'Updated']);
    }

    public function test_unauthorized_user_cannot_delete_workflow_rule()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('branch setup create'); // not delete

        $rule = WorkflowRule::create([
            'workflow_type' => 'Leave Request',
            'rule_key' => 'approval_threshold',
            'rule_value' => '5',
        ]);

        $response = $this->actingAs($user)->get("/dashboard/delete-workflow-settings/{$rule->id}");
        
        $response->assertStatus(403);
        $this->assertDatabaseHas('workflow_rules', ['id' => $rule->id]);
    }
}
