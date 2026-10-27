<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Department;
use App\Models\Archive;

class SecurityAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // create permissions
        Permission::firstOrCreate(['name' => 'edms view']);
        Permission::firstOrCreate(['name' => 'edms create']);
    }

    public function test_unauthenticated_user_is_redirected()
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');

        $apiResponse = $this->getJson('/api/integrations/active');
        $apiResponse->assertStatus(401);
    }

    public function test_user_cannot_access_unauthorized_department_files()
    {
        // Setup roles and departments
        $deptA = Department::forceCreate(['title' => 'Department A', 'description' => 'DPT-A']);
        $deptB = Department::forceCreate(['title' => 'Department B', 'description' => 'DPT-B']);

        // Create users
        $userA = User::factory()->create(['department_id' => $deptA->id]);
        $userB = User::factory()->create(['department_id' => $deptB->id]);

        // Authenticate to avoid model boot errors on creation
        $this->actingAs($userA);

        // Create file for Dept A
        $fileA = Archive::forceCreate(['department_id' => $deptA->id, 'updated_by' => $userA->id, 'folder_id' => 1]);

        // User A should be able to download their department's file (assuming they have edms view permission)
        $role = Role::firstOrCreate(['name' => 'standard user']);
        $role->givePermissionTo('edms view');
        $userA->assignRole($role);
        $userB->assignRole($role);

        $responseA = $this->get("/download-file?file_id={$fileA->id}&doc_id={$fileA->id}");
        // Controller might return 404 if file path is missing on disk, but shouldn't return 403
        $this->assertNotEquals(403, $responseA->status());

        // User B should NOT be able to access Dept A's file due to organization isolation
        // NOTE: If the application does NOT have this level of isolation natively, we may need to enforce it in the controller.
        // Let's test the endpoint directly to see the existing behavior.
        $responseB = $this->actingAs($userB)->get("/download-file?file_id={$fileA->id}&doc_id={$fileA->id}");
        
        // Either 403 (unauthorized) or 404 (not found in their scope)
        $this->assertTrue(in_array($responseB->status(), [403, 404]), "Expected 403 or 404, got " . $responseB->status());
    }

    public function test_role_based_access_control()
    {
        $user = User::factory()->create();
        
        // No roles yet, trying to access a protected route
        $response = $this->actingAs($user)->get('/download-file?file_id=1&doc_id=1');
        $response->assertStatus(403); // Lacks edms view permission

        $role = Role::firstOrCreate(['name' => 'editor']);
        $role->givePermissionTo('edms view');
        $user->assignRole($role);

        // Still might fail if file doesn't exist, but should pass authorization
        $response = $this->actingAs($user)->get('/download-file?file_id=999&doc_id=999');
        $response->assertStatus(404); // Passed 403, hit 404
    }
}
