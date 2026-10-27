<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\User;
use App\Models\DocumentTemplate;
use App\Models\Department;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Storage;

class Phase21TemplatesTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
        // Run roles seeder
        $this->seed(RoleSeeder::class);
    }

    /** @test */
    public function super_admin_can_create_and_manage_global_templates()
    {
        // 1. Setup User
        $user = User::factory()->create();
        $user->assignRole('super admin'); // Has necessary permissions

        // 2. Create a global template
        $response = $this->actingAs($user)->post('/dashboard/templates', [
            'title' => 'Global Memo Template',
            'type' => 'memo',
            'content_html' => '<h1>Global Memo</h1><p>Body</p>',
            'is_global' => true
        ]);

        $response->assertStatus(302);
        
        $template = DocumentTemplate::where('title', 'Global Memo Template')->first();
        $this->assertNotNull($template);
        $this->assertNull($template->department_id); // Ensure it's global
        $this->assertEquals(1, $template->version);

        // 3. Update template (Check version increment)
        $updateResponse = $this->actingAs($user)->put('/dashboard/templates/' . $template->id, [
            'title' => 'Updated Global Memo',
            'type' => 'memo',
            'content_html' => '<h1>Updated</h1>'
        ]);

        $updateResponse->assertStatus(302);
        $template->refresh();
        $this->assertEquals('Updated Global Memo', $template->title);
        $this->assertEquals(2, $template->version); // Version incremented

        // 4. Delete template
        $deleteResponse = $this->actingAs($user)->delete('/dashboard/templates/' . $template->id);
        $deleteResponse->assertStatus(302);
        $this->assertNull(DocumentTemplate::find($template->id));
    }

    /** @test */
    public function director_can_manage_department_templates_but_not_global()
    {
        $department1 = Department::forceCreate(['title' => 'IT Department', 'description' => 'IT', 'head' => 1]);
        $department2 = Department::forceCreate(['title' => 'HR Department', 'description' => 'HR', 'head' => 1]);

        $director1 = User::factory()->create(['department_id' => $department1->id]);
        $director1->assignRole('directors'); 

        $director2 = User::factory()->create(['department_id' => $department2->id]);
        $director2->assignRole('directors');

        // Director1 creates a template (automatically scoped to department)
        $this->actingAs($director1)->post('/dashboard/templates', [
            'title' => 'IT Department Form',
            'type' => 'form',
            'content_html' => '<p>IT Form</p>',
            'is_global' => true // Should be ignored since not super admin
        ]);

        $template = DocumentTemplate::where('title', 'IT Department Form')->first();
        $this->assertNotNull($template);
        $this->assertEquals($department1->id, $template->department_id); // Restricted to dept1

        // Director2 attempts to edit Director1's template (Should fail)
        $updateResponse = $this->actingAs($director2)->put('/dashboard/templates/' . $template->id, [
            'title' => 'Hacked IT Form',
            'type' => 'form',
            'content_html' => '<p>Hacked</p>'
        ]);
        
        // Controller returns back()->withErrors(['message' => 'Unauthorized...'])
        $updateResponse->assertSessionHasErrors(['message']);
        
        $template->refresh();
        $this->assertEquals('IT Department Form', $template->title); // Title remains unchanged
    }
}
