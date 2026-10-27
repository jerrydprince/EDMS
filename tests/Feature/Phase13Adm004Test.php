<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Department;
use App\Models\Workflow;
use App\Models\File;
use App\Models\Archive;
use App\Models\FilePath;
use Spatie\Permission\Models\Role;

class Phase13Adm004Test extends TestCase
{
    use RefreshDatabase;

    public function test_adm004_workflow_is_seeded_and_confidential()
    {
        \Illuminate\Database\Eloquent\Model::unguard();
        Department::create(['title' => 'Administration']);
        $this->artisan('db:seed', ['--class' => 'Phase13Adm004Seeder']);

        $workflow = Workflow::withoutGlobalScopes()->where('title', 'ADM-004 Discipline Workflow')->first();

        $this->assertNotNull($workflow);
        $this->assertTrue((bool)$workflow->is_confidential);
        
        $version = $workflow->versions()->first();
        $this->assertNotNull($version);
        $this->assertEquals(8, $version->steps()->count());
        $this->assertEquals(9, $version->transitions()->count());
    }

    public function test_confidential_scope_hides_workflow()
    {
        \Illuminate\Database\Eloquent\Model::unguard();
        
        $adminDept = Department::create(['title' => 'Administration']);
        
        $workflow = Workflow::create([
            'title' => 'Secret Workflow',
            'department_id' => $adminDept->id,
            'is_confidential' => true,
            'created_by' => 999
        ]);

        $standardUser = User::factory()->create(['department_id' => $adminDept->id]);
        
        $this->actingAs($standardUser);
        
        // Since standard user is not super admin/agf, and not the creator, scope should hide it
        $found = Workflow::find($workflow->id);
        $this->assertNull($found);

        // Creator should see it
        $creatorUser = User::factory()->create(['id' => 999, 'department_id' => $adminDept->id]);
        $this->actingAs($creatorUser);
        $foundAsCreator = Workflow::find($workflow->id);
        $this->assertNotNull($foundAsCreator);
    }

    public function test_confidential_file_download_is_restricted()
    {
        \Illuminate\Database\Eloquent\Model::unguard();
        
        $adminDept = Department::create(['title' => 'Administration']);
        
        $creatorUser = User::factory()->create(['id' => 999, 'department_id' => $adminDept->id]);
        $standardUser = User::factory()->create(['department_id' => $adminDept->id]);
        
        $permission = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'edms view']);
        $creatorUser->givePermissionTo($permission);
        $standardUser->givePermissionTo($permission);
        
        $this->actingAs($creatorUser);
        $folder = \App\Models\Folder::create(['name' => 'Test Folder', 'number' => '1234', 'path' => 'dummy/path', 'department_id' => $adminDept->id]);
        $archive = Archive::create(['department_id' => $adminDept->id, 'folder_id' => $folder->id, 'updated_by' => $creatorUser->id]);
        
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE files ADD COLUMN archive_id BIGINT UNSIGNED NULL');
        $filePath = \App\Models\FilePath::create(['path' => 'dummy.pdf', 'file_name' => 'dummy.pdf', 'file_size' => '1024', 'updated_by' => $creatorUser->id]);
        $archive->filePath()->attach($filePath->id);

        $file = new File([
            'is_confidential' => true,
            'created_by' => $creatorUser->id,
            'name' => 'test_file',
            'file_type' => 'document',
            'department_id' => $adminDept->id
        ]);
        $file->forceFill(['archive_id' => $archive->id])->save();
        
        $physicalDir = public_path('storage');
        if (!file_exists($physicalDir)) {
            mkdir($physicalDir, 0777, true);
        }
        $physicalPath = $physicalDir . '/dummy.pdf';
        file_put_contents($physicalPath, 'dummy content');

        // Standard user should get 403
        $this->actingAs($standardUser);
        $response = $this->get("/download-file?id={$archive->id}");
        if ($response->status() !== 403) {
            dump($response->getContent());
        }
        $response->assertStatus(403);

        // Creator should be allowed (it will log activity and return 200 to download)
        $this->actingAs($creatorUser);
        $response2 = $this->get("/download-file?id={$archive->id}");
        $response2->assertStatus(200);

        if (file_exists($physicalPath)) {
            unlink($physicalPath);
        }
    }
}
