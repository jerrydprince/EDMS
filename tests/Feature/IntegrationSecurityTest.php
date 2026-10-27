<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Department;
use App\Models\Archive;

class IntegrationSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_api_prevents_idor_on_delete_archive()
    {
        $deptA = new Department; $deptA->title = 'Dept A'; $deptA->save();
        $deptB = new Department; $deptB->title = 'Dept B'; $deptB->save();

        $userA = User::factory()->create(['department_id' => $deptA->id]);
        $this->actingAs($userA);
        
        $archiveB = new Archive; $archiveB->department_id = $deptB->id; $archiveB->folder_id = 1; $archiveB->updated_by = 1; $archiveB->save();

        $response = $this->get('/delete-archive?id=' . $archiveB->id);
        
        $this->assertFalse($response->status() === 200, 'User A should not be able to delete File B');
        $this->assertDatabaseHas('archives', ['id' => $archiveB->id]);
    }
}
