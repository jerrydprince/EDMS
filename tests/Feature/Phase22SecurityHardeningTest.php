<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use App\Models\Archive;
use App\Models\Department;
use App\Models\Division;
use App\Models\Unit;
use App\Models\DocumentType;
use App\Models\FilePath;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Storage;

class Phase22SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Make sure roles exist
        Role::firstOrCreate(['name' => 'super admin']);
        Role::firstOrCreate(['name' => 'super users']);
        Role::firstOrCreate(['name' => 'directors']);
        Role::firstOrCreate(['name' => 'division heads']);
        Role::firstOrCreate(['name' => 'unit heads']);
        Role::firstOrCreate(['name' => 'regular users']);
        
        $permissionView = Permission::firstOrCreate(['name' => 'edms view']);
        $permissionUpdate = Permission::firstOrCreate(['name' => 'edms update']);
        
        $permissionWorkflowView = Permission::firstOrCreate(['name' => 'workflow view']);
        
        Role::findByName('directors')->givePermissionTo(['edms view', 'edms update', 'workflow view']);
        Role::findByName('regular users')->givePermissionTo(['edms view', 'edms update', 'workflow view']);
        Role::findByName('super admin')->givePermissionTo(['edms view', 'edms update', 'workflow view']);
    }

    /** @test */
    public function cross_department_archive_download_is_forbidden_for_regular_user()
    {
        $this->withoutExceptionHandling();
        
        $dept1 = Department::forceCreate(['title' => 'Dept 1', 'description' => 'D1', 'head' => 1]);
        $dept2 = Department::forceCreate(['title' => 'Dept 2', 'description' => 'D2', 'head' => 2]);
        
        $user1 = User::factory()->create(['department_id' => $dept1->id]);
        $user1->assignRole('regular users');
        
        $this->actingAs($user1);
        
        $archive = Archive::forceCreate([
            'department_id' => $dept2->id,
            'folder_id' => 1,
            'updated_by' => 1,
        ]);
        
        $filePath = FilePath::forceCreate([
            'file_name' => 'test.pdf',
            'path' => '/storage/test.pdf',
            'file_size' => 1024,
            'updated_by' => 1,
            'updated_by_name' => 'Admin'
        ]);
        $archive->filePath()->attach($filePath->id);
        
        Storage::fake('public');
        Storage::disk('public')->put('storage/test.pdf', 'dummy content');
        
        // User1 is in Dept1. Archive is in Dept2. Should be 404 Not Found (due to DepartmentScope)
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
        
        $this->get('/download-file?id=' . $archive->id);
    }
    
    /** @test */
    public function super_admin_can_download_cross_department_archive()
    {
        $this->withoutExceptionHandling();
        
        $dept1 = Department::forceCreate(['title' => 'Dept 1', 'description' => 'D1', 'head' => 1]);
        $dept2 = Department::forceCreate(['title' => 'Dept 2', 'description' => 'D2', 'head' => 2]);
        
        $admin = User::factory()->create(['department_id' => $dept1->id]);
        $admin->assignRole('super admin');
        
        $this->actingAs($admin);
        
        $archive = Archive::forceCreate([
            'department_id' => $dept2->id,
            'folder_id' => 1,
            'updated_by' => 1,
        ]);
        
        $filePath = FilePath::forceCreate([
            'file_name' => 'test.pdf',
            'path' => '/storage/test.pdf',
            'file_size' => 1024,
            'updated_by' => 1,
            'updated_by_name' => 'Admin'
        ]);
        $archive->filePath()->attach($filePath->id);
        
        Storage::fake('public');
        Storage::disk('public')->put('storage/test.pdf', 'dummy content');
        
        // We expect it to throw 404 Not Found because the physical file doesn't exist in public_path
        // This proves it bypassed the 403 Forbidden AuthorizationException
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
        $this->get('/download-file?id=' . $archive->id);
        
        // Assuming file_exists check might fail in test env depending on public_path mocking, 
        // but it shouldn't be a 403. It might be 404 if file doesn't exist.
        $this->assertNotEquals(403, $response->getStatusCode());
    }

    /** @test */
    public function unassigned_user_cannot_view_task()
    {
        $this->withoutExceptionHandling();
        
        $dept1 = Department::forceCreate(['title' => 'Dept 1', 'description' => 'D1', 'head' => 1]);
        $dept2 = Department::forceCreate(['title' => 'Dept 2', 'description' => 'D2', 'head' => 2]);
        
        $user1 = User::factory()->create(['department_id' => $dept1->id]);
        $user1->assignRole('regular users');
        
        $user2 = User::factory()->create(['department_id' => $dept2->id]);
        $task = Task::forceCreate([
            'title' => 'Test Task',
            'description' => 'T',
            'due_date' => now(),
            'priority' => 'high',
            'status' => 'pending',
            'created_by' => $user2->id,
            'assigned_by_department' => $dept2->id
        ]);
        
        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);
        
        $this->actingAs($user1)->get('/dashboard/view-task/' . $task->id);
    }
    
    /** @test */
    public function api_mail_accounts_is_protected_by_sanctum()
    {
        // Unauthenticated access should be blocked
        $response = $this->getJson('/api/mail/accounts');
        $response->assertStatus(401);

        // Authenticated access should be allowed
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        $response = $this->getJson('/api/mail/accounts');
        $response->assertStatus(200);
    }

    /** @test */
    public function download_file_creates_audit_log()
    {
        $this->withoutExceptionHandling();
        
        $dept1 = Department::forceCreate(['title' => 'Dept 1', 'description' => 'D1', 'head' => 1]);
        $user1 = User::factory()->create(['department_id' => $dept1->id]);
        $user1->assignRole('regular users');
        
        $this->actingAs($user1);
        
        $archive = Archive::forceCreate([
            'department_id' => $dept1->id,
            'folder_id' => 1,
            'updated_by' => 1,
        ]);
        
        $filePath = FilePath::forceCreate([
            'file_name' => 'test_audit.pdf',
            'path' => '/storage/test_audit.pdf',
            'file_size' => 1024,
            'updated_by' => 1,
            'updated_by_name' => 'Admin'
        ]);
        $archive->filePath()->attach($filePath->id);
        
        // Create the physical file to bypass the 404 check
        $physicalDir = public_path('storage');
        if (!file_exists($physicalDir)) {
            mkdir($physicalDir, 0777, true);
        }
        $physicalPath = $physicalDir . '/test_audit.pdf';
        file_put_contents($physicalPath, 'dummy content');
        
        try {
            $this->get('/download-file?id=' . $archive->id);
        } catch (\Exception $e) {
            // catch standard exceptions depending on the download response
        } finally {
            // cleanup
            if (file_exists($physicalPath)) {
                unlink($physicalPath);
            }
        }
        
        // Assert file activity was logged
        $this->assertDatabaseHas('file_activity', [
            'archive_id' => $archive->id,
            'details' => 'Downloaded file',
        ]);
    }
}
