<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;

class AuditAndErrorPathTest extends TestCase
{
    use RefreshDatabase;

    public function test_validation_failures_on_api()
    {
        $user = User::factory()->create();
        
        $this->actingAs($user);
        
        // Attempt to login without credentials to check validation
        // Wait, since we are already authenticated, we should test an endpoint that requires validation, like creating custom integration
        $response = $this->postJson('/api/integrations/custom', []);
        
        // Should fail with 422 Unprocessable Entity due to missing required fields
        $response->assertStatus(422);
        
        // Specific validation error for 'name' should be present
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_error_paths_for_non_existent_entities()
    {
        $user = User::factory()->create();
        
        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'standard user']);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'edms view']);
        $role->givePermissionTo('edms view');
        $user->assignRole($role);
        
        $this->actingAs($user);
        
        // Attempting to download a file that doesn't exist (999999)
        $response = $this->get('/download-file?file_id=999999&doc_id=999999');
        
        // Should return 404 Not Found since it doesn't exist
        $response->assertStatus(404);
    }
}
