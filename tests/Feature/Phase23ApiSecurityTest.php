<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class Phase23ApiSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_routes_require_authentication()
    {
        // Unauthenticated access
        $response = $this->getJson('/api/mail/accounts');
        $response->assertStatus(401);

        $response = $this->getJson('/api/generic-workflow/tasks/1/state');
        $response->assertStatus(401);

        // Authenticated access
        $user = User::factory()->create();
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/mail/accounts');
        // Might be 200 or 403 depending on permissions, but not 401
        $this->assertNotEquals(401, $response->status());
    }
}
